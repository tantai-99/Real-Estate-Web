<?php
namespace Modules\V1api\Models\BApi;

class Eki extends AbstractBApi
{

    /**
     * @param EkiParams
     * @return JSON 駅グループ情報
     */
    public function getEkiWithEnsen(
        EkiParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_EKI, $params, $procName);
    }
}