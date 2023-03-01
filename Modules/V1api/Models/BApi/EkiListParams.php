<?php
namespace Modules\V1api\Models\BApi;
class EkiListParams extends AbstractParams
{
    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $media = 'pc';
    protected $ken_cd;
    protected $ensen_cd;
    protected $ensen_roman;
    protected $ensen_eki_cd;
    protected $ensen_eki_roman;

    /**
     * @param $ken_cd
     */
    public function setKenCd($ken_cd)
    {
        $this->ken_cd = $ken_cd;
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
    
    public function setEnsenEkiCd($ensen_eki_cd)
    {
        $this->ensen_eki_cd = $ensen_eki_cd;
    }
    
    public function setEnsenEkiRoman($ensen_eki_roman)
    {
        $this->ensen_eki_roman = str_replace("-", ":", $ensen_eki_roman);
    }
}