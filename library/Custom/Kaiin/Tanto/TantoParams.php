<?php
namespace Library\Custom\Kaiin\Tanto;

use Library\Custom\Kaiin\AbstractParams;

class TantoParams extends AbstractParams
{

    // APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。

    // APIパラメータに使用されない
    // 変則的に使用される変数はprivateで定義。
    protected $TantoCd;

    public function setTantoCd($tanto_cd)
    {
        $this->TantoCd = $tanto_cd;
    }
    
    public function getTantoCd()
    {
        return $this->TantoCd;
    }
}