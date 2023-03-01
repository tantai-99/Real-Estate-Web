<?php
namespace Modules\V1api\Models\BApi;

class Choson extends AbstractBApi
{
    public function getChoson(
        ChosonParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_CHOSON, $params, $procName);
    }

    public function getChosonList(
        ChosonListParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_CHOSON_LIST, $params, $procName);
    }
}