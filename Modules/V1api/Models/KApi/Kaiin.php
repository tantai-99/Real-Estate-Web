<?php
namespace Modules\V1api\Models\KApi;

class Kaiin extends AbstractKApi
{

    /**
     * @param KaiinParams
     * @return JSON 
     */
    public function get(
        KaiinParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            return json_decode(@file_get_contents(dirname(__FILE__) . '/kaiin.json'), true)['model'];
        }

        $url = $this::URL_FUNC_KAIIN . $params->getKaiinNo();
        return $this->http_get($url , $params, $procName)['model'];
    }
}