<?php
namespace Modules\V1api\Models\BApi;

class ChosonListParams extends AbstractParams
{
    const GROUPING_TYPE_LOCATE_CD = 'locate_cd';

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $ken_cd;
    protected $shikugun_cd;
    protected $oaza_fl = 1;
    protected $choaza_fl = 1;
    protected $kana_nm_sort_fl = 1;

    /**
     * @param $ken_cd
     */
    public function setKenCd($ken_cd)
    {
        $this->ken_cd = $ken_cd;
    }

    public function setShikugunCd($shikugun_cd)
    {
        $this->shikugun_cd = $shikugun_cd;
    }
}