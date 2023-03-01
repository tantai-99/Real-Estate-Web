<?php
namespace Modules\V1api\Models\BApi;

class EkiList extends AbstractBApi
{

    /**
     * @param EkiListParams
     * @return JSON 沿線
     */
    public function getEki(
        EkiListParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_EKI_LIST, $params, $procName);
    }
}