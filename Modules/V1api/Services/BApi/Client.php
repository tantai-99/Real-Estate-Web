<?php
namespace Modules\V1api\Services\BApi;

use Modules\V1api\Models\BApi;
use Modules\V1api\Exceptions;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
class Client {
    
    private static $instance;
    protected $logger;
    protected $_config;
    protected $_debug = array();
    
    public function __construct() {
        
        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }
    
    /**
     * 
     * @param string $path
     * @param array|object|string $params
     */
    public function get($path, $params, $procName = '') {
        $url = $this->_buildUrl($path);
        $query = $this->_buildQuery($params);
        $path = $url . '?' . $query;
        $request = app('request');
        // リクエスト
        $response = null;
		$action_id = strtoupper(getControllerName()) . '-'. strtoupper(getActionName());
		if (!empty($procName)) $action_id .= '-'. $procName;
        $user_id = $request->has('com_id') ? $request->com_id : 'cms';
        try {
            $time_start = microtime(true);
            $request_header = [
            		"bknapi-system-id" => "hpadvance-sers",
            		"bknapi-action-id" => $action_id,
                    "bknapi-user-id" => $user_id,
                    "Connection" => "keep_alive"
            ];
            $response = $this->call($path, $request_header);
        } catch (\Exception $e) {
        	$this->logger->debug("<BAPI ERR> = " . $e->getMessage());
            $this->logger->error("<BAPI ERR> = " . $e->getMessage());
            throw new Exceptions\BApi(Exceptions\BApi::DEFAULT_ERR, $e->getMessage(), 0, $e);
        } finally {
            $timelimit = microtime(true) - $time_start;
            $this->logger->debug("<BAPI> ${timelimit} sec. user=${user_id} action=${action_id} URL=${path}");
            $this->_debug += array($action_id => $path);
        }
        return $response;
    }
    
    private function call($url, $headers)
    {
        // NHP-5107: PC詳細かつ、右サイド『最近見た物件』の場合のみ特殊処理を実施する
        $detailHistoryFlg = false;
        if(preg_match('{/' . BApi\AbstractBApi::URL_FUNC_BUKKEN_SEARCH . '}', $url)) {
            $params = app('request')->all();
            if( getModuleName() == 'v1api'
             && getControllerName() == 'search'
             && getActionName() == 'detail'
             && $params['media'] == 'pc' ) {
                $detailHistoryFlg = true;
            }
        }
    	try {
            $timeOut = 30;
            if ($detailHistoryFlg) {
                // NHP-5107: APIのタイムアウト値を短くしシステムエラーが発生しない時間内に強制的にエラーにしてしまう
                $timeOut = 15; // NHP-5120 12s -> 15s
            }
            $client = HttpClient::create([
                'max_redirects' => 0,
                'timeout'      => $timeOut
           ]);
            $response = $client->request('GET', $url, ['headers' => $headers]);
    		return new Response([
    		                'content' => $response->getContent(),
    		                'headers'  => $response->getHeaders(),
    						'status'  => $response->getStatusCode(),
    						// 'message'  => $response->getMessage (),
    		            ]);
    	} catch (TransportExceptionInterface $e) {
            if ($detailHistoryFlg) {
                // NHP-5107: 最近見た物件が0件同等の結果を返す
                $bukkenZeroRes = [
                    'bukkens' => [],
                    'current_page' => 1,
                    'total_pages' => 0,
                    'per_page' => 5,
                    'facets' => new \stdClass(),
                    'pivot_facets' => new \stdClass(),
                    'errors' => [],
                    'total_count' => 0
                ];
                return new Response([
                    'content' => json_encode($bukkenZeroRes),
                    'headers'  => [],
                    'status'  => 200,
                    'message' => 'OK'
                ]);
            }
    		error_log($e);
    		throw $e;
    	}
    }
    
    public function _buildUrl($path) {
        return $this->_config->base_url . $path;
    }
    
    /**
     * 物件API用パラメータフォーマットに変換する
     * @param array|object $params
     * @return array
     */
    protected function _buildQuery($params) {
        // 文字列の場合はそのまま返す
        if (is_string($params)) {
            return preg_replace('/^\?/', '', $params);
        }
        
        $result = [];
        foreach ($params as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            else if (is_bool($value)) {
                $value = (int) $value;
            }
            $result[$name] = $value;
        }
        return http_build_query($result);
    }

    /**
     * デバッグ用の連想配列を返す。
     * @return array
     */
    public function debug() {
        return $this->_debug;
    }

    /**
     * このクラスのインスタンスを返します。
     *
     * @return インスタンス
     */
    public static function getInstance()
    {
        if (is_null(static::$instance))
        {
            static::$instance = new Client();
        }
        return static::$instance;
    }
}