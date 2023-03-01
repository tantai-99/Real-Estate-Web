<?php
namespace Modules\V1api\Models\BApi;

class EkiParams extends AbstractParams
{
    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $target = 'engine_rental';
    protected $media = 'pc';
    protected $kaiin_link_no;
    protected $csite_bukken_shumoku_cd;
    protected $ken_cd;
    protected $ensen_cd;
    protected $ensen_roman;
    protected $ensen_eki_cd;
    protected $group_id;

    /**
     * @param $kaiin_link_no
     */
    public function setKaiinLinkNo ($kaiin_link_no)
    {
        $this->kaiin_link_no = $kaiin_link_no;
    }
    
    /**
     * @param $csite_bukken_shumoku_cd 検索用物件種目コードの配列
     */
    public function setCsiteBukkenShumoku ($csite_bukken_shumoku_cd)
    {
        $this->csite_bukken_shumoku_cd = $csite_bukken_shumoku_cd;
    }

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

    public function setGroupId($group_id)
    {
        if (isset($this->getConfig()->dummy_bapi_group_id)) {
    		$this->group_id = $this->getConfig()->dummy_bapi_group_id;
    	} else {
    		$this->group_id = $group_id;
    	}
    }
}