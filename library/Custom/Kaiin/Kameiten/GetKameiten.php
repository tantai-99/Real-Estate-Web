<?php
namespace Library\Custom\Kaiin\Kameiten;
use Library\Custom\Kaiin\AbstractKaiin;
use Library\Custom\Kaiin\Kameiten\KameitenParams;
use Library\Custom\Kaiin\AbstractParams;

class GetKameiten extends AbstractKaiin
{

    protected $model;
    protected $pagination;


    /**
     * @param Library\Custom\Kaiin\Kameiten\KameitenParams
     * @return JSON 
     */
    public function get(
        KameitenParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            $rows = json_decode(@file_get_contents(dirname(__FILE__) . '/Kameiten.json'), true);
            $this->pagination = $rows['pagination'];
            return $this->model = $rows['model'];
        }

        $url = $this::URL_FUNC_KAMEITEN;
        $rows = $this->http_get($url , $params, $procName);
        $this->pagination = $rows['pagination'];
        return $this->model = (isset($rows['model'])) ? $rows['model'] : array();
    }

    public function getPagination() {
        return $this->pagination;
    }

    // GET用関数
    protected function http_get (
        $url_func, AbstractParams $params, $procName = '')
    {
        $data_url = $params->buildQuery($params);
        $res = $this->getClient()->get($url_func, $data_url, $procName);
        //０件お場合の処理
        $res->data['model'] = (object) array();
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