<?php
namespace Modules\V1api\Models\KApi;

use Modules\V1api\Services\KApi;

abstract class AbstractKApi
{
    protected $logger;
    protected $_config;
    
    const URL_FUNC_KAIIN      = '/api/kaiin/';
    const URL_FUNC_KAIIN_LIST      = '/api/kaiin/List';

    public function __construct()
    {

        // コンフィグ取得
        $this->_config = getConfigs('v1api.api');
        $this->logger = \Log::channel('debug');
    }
    
    public function getClient() {
        return KApi\Client::getInstance();
    }
    
    // GET用関数
    protected function http_get (
        $url_func, $params, $procName = '')
    {
        $data_url = $params->buildQuery($params);

        $res = $this->getClient()->get($url_func, $data_url, $procName);

        // 4697 Check Kaiin Stop
        if ($res['headers']['response_code'] >= 400 && $res['headers']['response_code'] < 600) {
            return false;
        }
        // 失敗していたらエラーを投げる
        $res->ifFailedThenThrowException();
        $result = json_decode($res['content'], true);
        if (isset($result['warnings']))
        {
            $this->logger->error("<KAPI WARN> " . print_r($result['warnings'], true));
            $this->logger->debug("<KAPI WARN> " . print_r($result['warnings'], true));
        }
        return $result;
    }
}