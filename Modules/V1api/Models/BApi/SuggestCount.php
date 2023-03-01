<?php
namespace Modules\V1api\Models\BApi;

class SuggestCount extends AbstractBApi
{

    /**
     * @param SuggestCountParams
     * @return JSON
     */
    public function suggest(
        SuggestCountParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_BUKKEN_SUGGEST, $params, $procName);
    }

    /**
     * @param SuggestCountParams
     * @return JSON
     */
    public function count(
        SuggestCountParams $params, $procName = '')
    {
        return $this->http_get($this::URL_FUNC_BUKKEN_COUNT, $params, $procName);
    }
}