<?php
namespace Modules\V1api\Models\BApi;

class SuggestCountParams extends AbstractParams
{
    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
    /** 物件ID */
    protected $id;
    protected $group_id;
    protected $kaiin_link_no;
    protected $csite_bukken_shumoku_cd;
    protected $shozaichi_cd;
    protected $ensen_cd;
    protected $ensen_eki_cd;
    
	protected $fulltext;
    
    protected $osusume_kokai_fl;

    protected $fulltext_fields;

    protected $fulltext_type;
    protected $data_model_fulltext_fields;
    protected $or = [];

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

    public function setShozaichiCd($shozaichi_cd)
    {
        $this->shozaichi_cd = $shozaichi_cd;
    }

    public function setEnsenCd($ensen_cd)
    {
        $this->ensen_cd = $ensen_cd;
    }

    public function setEnsenEkiCd($ensen_eki_cd)
    {
        $this->ensen_eki_cd = $ensen_eki_cd;
    }

    public function setId($bukken_id)
    {
        $this->id = $bukken_id;
    }

    /**
     * アドバンス加盟店ID
     */
    public function setGroupId($group_id)
    {
    	if (isset($this->getConfig()->dummy_bapi_group_id)) {
    		$this->group_id = $this->getConfig()->dummy_bapi_group_id;
    	} else {
    		$this->group_id = $group_id;
    	}
    }
    
    public function setOsusumeKokaiFl($osusume_kokai_fl)
    {
        $this->osusume_kokai_fl = $osusume_kokai_fl;
    }

    public function setFacetsOnly() {
    	$this->facets_only = 1;
    	$this->data_model = null;
    }

    public function setFulltext($fulltext)
    {
        $this->fulltext = $fulltext;
    }
    
    public function setFulltextFields() {
        $this->fulltext_fields = [
            'detail.tatemono_kozo', 'ijihito',
            'csite_bikos','csite_tokkis', 'csite_setsubis',
            'csite_chikunengetsu', 'chisei',
            'csite_jokento', 'csite_kaidate_kai',
            'torihiki_taiyo', 'hikiwatashi',
            'hokento', 'kanri_keitai',
            'madori', 'pet', 'reform',
            'renovation', 'setback', 'shikugun_nm',
            'shumoku_nm', 'sokosu', 'sonota_ichijikin',
            'inspection', 'madori_uchiwake',
            'onsen_hikikomi_jokyo', 'onsen_hiyo_to',
            'onsen_riyo_keitai', 'reform_renovation_ka',
            'setsudo_jokyo', 'shuyo_saikomen',
            'csite_kotsus', 'csite_kotsus_for_highlight',
            'csite_shozaichi', 'chushajo','csite_tatemono_nm',
            'staff_comment_both', 'ippan_message_shosai_both',
            'credit_kessai', 'csite_bikos.biko_both',
        ];
    }

    public function setDataModelFulltextFields() {
        $this->data_model_fulltext_fields = [
            'images.caption',
            'shuhen_kankyos.caption',
            'shuhen_kankyos.nm_both',
            'shuhen_kankyos.shubetsu_nm',
        ];
    }
    
    public function setConditionAllSearch($or) {
        $this->or[] = $or;
    }
}