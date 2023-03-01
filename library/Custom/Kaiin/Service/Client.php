<?php
namespace Library\Custom\Kaiin\Service;

use Modules\V1api\Exceptions;

class Client {
    
    private static $instance;
    protected $logger;
    protected $_config;
    protected $_debug = array();
    
    public function __construct() {
        
        // コンフィグ取得
        $this->_config = getConfigs('kaiin_api');
        $this->logger = \Log::channel('debug');
    }
    
    /**
     * 
     * @param string $path
     * @param array|object|string $params
     * @return Custom\Kaiin\Service\Response
     */
    public function get($path, $params, $procName = '', $arrayFlg=false, $isBreak = true) {
        $url = $this->_buildUrl($path);
        if (!$arrayFlg) {
            $query = $this->_buildQuery($params);
        } else {
            $query = $params;
        }
        if ($query == "") {
            $path = $url;
        }else{
            $path = $url . '?' . $query;
        }
        // リクエスト
        $result = null;
        try {
            $time_start = microtime(true);            
            $http_response_header = null;
            $result = new Response([
                'content' => @file_get_contents($path),
                'headers'  => $http_response_header,
                'isBreak'    => $isBreak
            ]);
        } catch (Exception $e) {
            $this->logger->error("<KAPI ERR> = " . $e->getMessage());
            throw $e;
        } finally {
            $timelimit = microtime(true) - $time_start;
            $this->logger->debug("<KAPI> ${timelimit} sec. process=${procName} URL=" . $path);
            $this->_debug += array($procName => $path);
            $pos = strpos($http_response_header[0], '200');
            if ($pos === false) {
                // 例外処理
                throw new Exceptions\KApi(Exceptions\KApi::DEFAULT_ERR, 'Failed to connect KAPI. response status is ' . $http_response_header[0]);
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
        if (is_null(Client::$instance))
        {
            Client::$instance = new Client();
        }
        return Client::$instance;
    }
}