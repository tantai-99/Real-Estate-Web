<?php
namespace Modules\V1api\Models\BApi;

class ChosonParams extends AbstractParams
{
    const GROUPING_TYPE_LOCATE_CD = 'locate_cd';

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    protected $target = 'engine_rental';
//    protected $media = 'pc';
    protected $kaiin_link_no;
    protected $group_id;
    protected $csite_bukken_shumoku_cd;
    protected $ken_cd;
    protected $shikugun_cd;
    protected $oaza_fl = 1;
    protected $choaza_fl = 1;
    protected $kana_nm_sort_fl = 1;

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

    public function setShikugunCd($shikugun_cd)
    {
        $this->shikugun_cd = $shikugun_cd;
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