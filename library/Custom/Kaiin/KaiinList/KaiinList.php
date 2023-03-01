<?php
namespace Library\Custom\Kaiin\KaiinList;

use Library\Custom\Kaiin\AbstractKaiin;
use Library\Custom\Kaiin\KaiinList\KaiinListParams;

class KaiinList extends AbstractKaiin
{

    /**
     * @param Library\Custom\Kaiin\KaiinSummary\KaiinSummaryParams
     * @return JSON 
     */
    public function get(
        KaiinListParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->info("<KAPI> dummy connect.");
            return json_decode(@file_get_contents(dirname(__FILE__) . '/KaiinList.json'), true)['model'];
        }

        $url = $this::URL_FUNC_KAIIN_LIST;
        return $this->http_get($url , $params, $procName)['model'];
    }
}