<?php
namespace Library\Custom\Kaiin\RemarcKeiyaku;
use Library\Custom\Kaiin\AbstractKaiin;
class GetRemarcKeiyaku extends AbstractKaiin
{

    /**
     * @param RemarcKeiyakuParams
     * @return JSON 
     */
    public function get(
        RemarcKeiyakuParams $params, $procName = '')
    {
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            return json_decode(@file_get_contents(dirname(__FILE__) . '/RemarcKeiyaku.json'), true)['model'];
        }
        $url = $this::URL_FUNC_REMARCKEIYAKU.$params->getKaiinNo();
        
        $model = [];
        try{
            $model = $this->http_get($url , $params, $procName)['model'];
        }catch (\Exception $e){
            $model = [];
        }
        return $model;
    }
}