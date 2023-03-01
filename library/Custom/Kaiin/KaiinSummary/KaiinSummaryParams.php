<?php
namespace Library\Custom\Kaiin\KaiinSummary;

use Library\Custom\Kaiin\AbstractParams;

class KaiinSummaryParams extends AbstractParams
{

    // APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。

    // APIパラメータに使用されない
    // 変則的に使用される変数はprivateで定義。
    private $kaiinNo;

    public function setKaiinNo($kaiin_no)
    {
        $this->kaiinNo = $kaiin_no;
    }
    
    public function getKaiinNo()
    {
        return $this->kaiinNo;
    }
}