<?php
namespace Modules\V1api\Models\BApi;

class ShikugunListParams extends AbstractParams
{
    const GROUPING_TYPE_LOCATE_CD = 'locate_cd';

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $ken_cd;
    protected $shozaichi_cd;
    protected $shikugun_roman;
    protected $locate_roman;
    protected $grouping;
    protected $media = 'pc';

    /**
     * @param $ken_cd
     */
    public function setKenCd($ken_cd)
    {
        $this->ken_cd = $ken_cd;
    }
    
    // GROUPING_TYPE_LOCATE_CD
    public function setGrouping($groupingType)
    {
        $this->grouping = $groupingType;
    }
    
    public function setShozaichiCd($shozaichi_cd)
    {
        $this->shozaichi_cd = $shozaichi_cd;
    }
    
    public function setShikugunRoman($shikugun_roman)
    {
        $this->shikugun_roman = $shikugun_roman;
    }
    
    public function setLocateRoman($locate_roman)
    {
        $this->locate_roman = $locate_roman;
    }
}