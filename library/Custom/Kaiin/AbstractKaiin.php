<?php
namespace Library\Custom\Kaiin;

use Library\Custom\Kaiin\Service\Client;

abstract class AbstractKaiin
{
    protected $logger;
    protected $_config;
    
    const URL_FUNC_KAIIN         = '/api/kaiin/';
    const URL_FUNC_KAIIN_LIST    = '/api/kaiin/List';
    const URL_FUNC_KAIIN_SUMMARY = '/api/kaiinSummary/';
    const URL_FUNC_TANTO         = '/api/Tanto';
    const URL_FUNC_KAMEITEN      = '/api/Service/Search/Kameiten_Kaiin';
    const URL_FUNC_REMARCKEIYAKU = '/api/RemarcKeiyaku/';
    const URL_FUNC_REMARCKEIYAKU_LIST = 'api/RemarcKeiyaku/List/ByKaiinNos';

    public function __construct()
    {
        // コンフィグ取得
        $this->_config = getConfigs('kaiin_api');
        $this->logger = \Log::channel('debug');
    }
    
    public function getClient() {
        return Client::getInstance();
    }
    
    // GET用関数
    protected function http_get (
        $url_func, AbstractParams $params, $procName = '')
    {
        $data_url = $params->buildQuery($params);
        $res = $this->getClient()->get($url_func, $data_url, $procName);
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

    protected function http_gets ($url_func, $params, $procName = '', $isBreak = true)
    {
        $data_url = http_build_query($params);
        $res = $this->getClient()->get($url_func, $data_url, $procName, true, $isBreak);
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