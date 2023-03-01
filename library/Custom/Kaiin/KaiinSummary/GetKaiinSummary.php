<?php
namespace Library\Custom\Kaiin\KaiinSummary;

use Library\Custom\Kaiin\AbstractKaiin;

class GetKaiinSummary extends AbstractKaiin
{

    /**
     * @param Library\Custom\Kaiin\KaiinSummary\KaiinSummaryParams
     * @return JSON 
     */
    public function get(
        KaiinSummaryParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            return json_decode(@file_get_contents(dirname(__FILE__) . '/KaiinSummary.json'), true)['model'];
        }

        $url = $this::URL_FUNC_KAIIN_SUMMARY . $params->getKaiinNo();
        return $this->http_get($url , $params, $procName)['model'];
    }
}