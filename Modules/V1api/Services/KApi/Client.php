<?php
namespace Modules\V1api\Services\KApi;

use Modules\V1api\Exceptions;

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
        
        // リクエスト
        $result = null;
        try {
            $time_start = microtime(true);            
            $http_response_header = null;
            $result = new Response([
                'content' => @file_get_contents($path),
                'headers'  => $http_response_header
            ]);
        } catch (\Exception $e) {
            $this->logger->error("<KAPI ERR> = " . $e->getMessage());
            throw $e;
        } finally {
            $timelimit = microtime(true) - $time_start;
            $this->logger->debug("<KAPI> ${timelimit} sec. process=${procName} URL=" . $path);
            $this->_debug += array($procName => $path);
            $pos = strpos($http_response_header[0], '200');
            if ($pos === false) {
                // 例外処理
                preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#",$http_response_header[0], $out);
                if (!(intval($out[1]) >= 400 && intval($out[1]) < 600)) {
                    throw new Exceptions\KApi(Exceptions\KApi::DEFAULT_ERR, 'Failed to connect KAPI. response status is ' . $http_response_header[0]);
                }
            }
        }
        return $result;
    }
    
    public function _buildUrl($path) {
        return $this->_config->kaiin_base_url . $path;
    }
    
    /**
     * API用パラメータフォーマットに変換する
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