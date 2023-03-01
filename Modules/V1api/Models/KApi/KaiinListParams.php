<?php
namespace Modules\V1api\Models\KApi;

class KaiinListParams extends AbstractParams
{

    // APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。

    // APIパラメータに使用されない
    // 変則的に使用される変数はprivateで定義。
    protected $kaiinNos;

    public function setKaiinNos($kaiinNos)
    {
        $this->kaiinNos = $kaiinNos;
    }
}