<?php
namespace Modules\V1api\Models\BApi;

class Ensen extends AbstractBApi
{

    /**
     * @param EnsenParams
     * @return JSON 沿線グループ情報
     */
    public function getEnsenWithGroup(
        EnsenParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_ENSEN, $params, $procName);
    }
}