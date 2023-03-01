<?php
namespace Modules\V1api\Models\KApi;

class KaiinList extends AbstractKApi
{

    /**
     * @param KaiinListParams
     * @return JSON 
     */
    public function get(
        KaiinListParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            $result = array(json_decode(@file_get_contents(dirname(__FILE__) . '/kaiin.json'), true))[0];
        } else {
            // 4697 Check Kaiin Stop
            $result = $this->http_get($this::URL_FUNC_KAIIN_LIST, $params, $procName);
        }
        if (!isset($result['model'])) {
            return false;
        }
        return $result['model'];
    }
}