<?php
namespace Modules\V1api\Models\BApi;

class Shikugun extends AbstractBApi
{

    /**
     * @param ShikugunParams
     * @return JSON locate_cd付き市区町村
     */
    public function getShikugunWithLocateCd(
        ShikugunParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_SHIKUGUN, $params, $procName);
    }
}