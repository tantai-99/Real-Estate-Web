<?php
namespace Modules\V1api\Models\BApi;

class EnsenListParams extends AbstractParams
{
    const GROUPING_TYPE_TRUE = true;

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $media = 'pc';
    protected $ken_cd;
    protected $grouping;
    protected $ensen_cd;
    protected $ensen_roman;

    /**
     * @param $ken_cd
     */
    public function setKenCd($ken_cd)
    {
        $this->ken_cd = $ken_cd;
    }
    
    public function setGrouping($groupingType)
    {
        $this->grouping = $groupingType;
    }
    
    public function setEnsenCd($ensen_cd)
    {
        $this->ensen_cd = $ensen_cd;
    }
    
    public function setEnsenRoman($ensen_roman)
    {
        if(is_array($ensen_roman)) {
            $unique = array_unique($ensen_roman);
            $ensen_roman = array_values($unique);
        }
        $this->ensen_roman = $ensen_roman;
    }
}