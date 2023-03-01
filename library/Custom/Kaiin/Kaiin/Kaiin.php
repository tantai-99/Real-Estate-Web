<?php
namespace Library\Custom\Kaiin\Kaiin;

use Library\Custom\Kaiin\AbstractKaiin;
use Library\Custom\Kaiin\AbstractParams;
use Modules\V1api\Exceptions;

class Kaiin extends AbstractKaiin
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Library\Custom\Kaiin\KaiinSummary\KaiinSummaryParams
     * @return JSON
     */
    public function get(
        KaiinParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            $model = json_decode(@file_get_contents(dirname(__FILE__) . '/Kaiin.json'), true)['model'];
            $model['kaiinNo'] = $params->getKaiinNo();

            return $model;
        }
        $url = AbstractKaiin::URL_FUNC_KAIIN . $params->getKaiinNo();

        $model = [];
        try{
            $model = $this->http_get($url , $params, $procName)['model'];
        }catch (Exceptions\KApi $e){
            $model = [];
        }
        return $model;
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


}