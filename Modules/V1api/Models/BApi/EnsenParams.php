<?php
namespace Modules\V1api\Models\BApi;

class EnsenParams extends AbstractParams
{
    const GROUPING_TYPE_TRUE = true;

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    // private $paramas;
    protected $target = 'engine_rental';
    protected $media = 'pc';
    protected $kaiin_link_no;
    protected $group_id;
    protected $csite_bukken_shumoku_cd;
    protected $ken_cd;
    protected $grouping;
    protected $ensen_cd;
    protected $ensen_roman;

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
    
    // GROUPING_TYPE_TRUE
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

    public function setGroupId($group_id)
    {
        if (isset($this->getConfig()->dummy_bapi_group_id)) {
    		$this->group_id = $this->getConfig()->dummy_bapi_group_id;
    	} else {
    		$this->group_id = $group_id;
    	}
    }
    
    // ATHOME_HP_DEV-4901 沿線駅一覧の沿線毎の物件数取得方法を改善する
    public function setEnsenEkiCd($ensen_eki_cd)
    {
        $this->ensen_eki_cd = $ensen_eki_cd;
    }
}
