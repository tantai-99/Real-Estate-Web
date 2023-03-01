<?php
namespace Modules\V1api\Models\BApi;

class EnsenList extends AbstractBApi
{

    /**
     * @param EnsenListParams
     * @return JSON 沿線
     */
    public function getEnsen(
        EnsenListParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_ENSEN_LIST, $params, $procName);
    }
}