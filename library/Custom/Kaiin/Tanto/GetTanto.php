<?php
namespace Library\Custom\Kaiin\Tanto;

use Library\Custom\Kaiin\AbstractKaiin;

class GetTanto extends AbstractKaiin
{

    /**
     * @param Library\Custom\Kaiin\TantoParams
     * @return JSON 
     */
    public function get(
        TantoParams $params, $procName = '')
    {
        // 環境による切り替え
        $dummy = isset($this->_config->dummy_kapi) ? $this->_config->dummy_kapi : false;
        if ($dummy) {
            $this->logger->debug("<KAPI> dummy connect.");
            return json_decode(@file_get_contents(dirname(__FILE__) . '/Tanto.json'), true)['model'];
        }
        $url = AbstractKaiin::URL_FUNC_TANTO;
        
        $model = [];
        try{
            $model = $this->http_get($url , $params, $procName)['model'];
        }catch (\Exception $e){
            $model = [];
        }
        return $model;
    }
}