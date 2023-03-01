<?php
namespace Modules\V1api\Models\BApi;

class ShikugunList extends AbstractBApi
{

    /**
     * @param ShikugunListParams
     * @return JSON 市区町村
     */
    public function getShikugun(
        ShikugunListParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_SHIKUGUN_LIST, $params, $procName);
    }
}