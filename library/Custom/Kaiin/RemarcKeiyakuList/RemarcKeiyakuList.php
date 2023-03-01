<?php
namespace Library\Custom\Kaiin\RemarcKeiyakuList;
use Library\Custom\Kaiin\AbstractKaiin;

class RemarcKeiyakuList extends AbstractKaiin
{

    /**
     * @param _RemarcKeiyakuListParams
     * @return JSON 
     */
    public function get(
        $params, $procName = '', $isBreak = true)
    {
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            if (!$isBreak) {
                return json_decode(@file_get_contents(dirname(__FILE__) . '/RemarcKeiyakuList.json'), true);
            }
            return json_decode(@file_get_contents(dirname(__FILE__) . '/RemarcKeiyakuList.json'), true)['model'];
        }

        $index = 0;
        foreach ($params->kaiinNos as $key => $value) {
            if(preg_match('/^\d{8,8}$/', $value)) {
                $data["kaiinNos[$index]"] = $value;
                $index++;
            } else {
                $this->logger->debug('No. Error format: '. $value);
            }
        }

        $url = $this::URL_FUNC_REMARCKEIYAKU_LIST;
        if (!$isBreak) {
            return $this->http_gets($url , $data, $procName, $isBreak);
        }
        return $this->http_gets($url , $data, $procName)['model'];
    }
}