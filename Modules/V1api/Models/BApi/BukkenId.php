<?php
namespace Modules\V1api\Models\BApi;

class BukkenId extends AbstractBApi
{

    /**
     * @param BukkenIdParams
     * @return JSON 
     */
    public function search(
        BukkenIdParams $params, $procName = '')
    {
        $url = $this::URL_FUNC_BUKKEN . $params->getId() . '.json';
        return $this->http_get($url , $params, $procName);
    }
}