<?php
namespace Modules\V1api\Models\BApi;

class BukkenSearchParams extends AbstractParams
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
    
    protected $per_page;
    protected $page;
    protected $order_by;
	protected $fulltext;
    
    protected $facets_only;
    protected $osusume_kokai_fl;
    protected $chizu_hyoji_ka_fl;

    protected $bukken_no;

    protected $data_model = [
        'images', 'ippan_kokai_message',
        'kenchiku_joken_tsuki_fl', // 建築条件付き
        'saiteki_yoto_nm',
        'yoto_chiiki_nm',
    	'hikiwatashi_joken_nm',
    	'chikugo_minyukyo_fl',
    	'jishatasha_cd',     // 自社他社コード：２次広告判定用
        'kakaku_hikokai_fl', // 価格非公開フラグ
        // 物件反響メールのバス情報用
        'kotsus',
        'chizu_kensaku_fuka_fl',
        'tatemono_nm',
        'kaiin_muke_tatemono_hihyoji_fl',
        'heya_no',
        'kaiin_muke_heya_no_hihyoji_fl',
        'kukaku_no',
        'kai_kukaku_no_hihyoji_fl',
        'goto_no',
        'kai_goto_no_hihyoji_fl'
    ];

    // #3015 add params display_model
    protected $display_model = [
        'id','kanrihito','shikikin',
        'kakaku','hoshokin','reikin',
        'madori','tatemono_ms','shumoku_nm',
        'csite_chikunengetsu','csite_bukken_title',
        'csite_bukken_shumoku_cd','toho','csite_kotsus',
        'csite_shozaichi','csite_kakaku',
        'csite_kanrihito','csite_shikikin',
        'csite_hoshokin','csite_reikin',
        'tatemono_ms','tatemono_tsubo_su',
        'csite_tsubo_tanka','tsubo_tanka',
        'csite_kaidate_kai','tochi_ms',
        'tochi_tsubo_su','kenpei_ritsu',
        'yoseki_ritsu','tatemono_kozo',
        'csite_shido_futan_ms',
        'new_mark_fl_for_c','csite_panorama_kokai_fl',
        'shinchiku_chuko_cd','csite_shashin_jujitsu_fl',
        'niji_kokoku_jido_kokai_fl','csite_images_madori_last',
        'csite_images_gaikan_first','ensen_eki_nashi_fl',
        'panorama_webvr_fl', 'panorama_contents_cd',    // パノラマ種別判定用(NHP-4591)
        'matching_level_cd', 'csite_muke_kaiin_no',     // fdp add
        'ido', 'keido', // 4689 check lat, lon
        'staff_comment',
        // 4835 
        'bukken_no', 'kokaichu_kokais', 'kanri_no', 'tatemono_nm',
        'image_cnt', 'csite_image_madori', 'ken_cd', 'joi_shumoku_cd',
        'csite_image_for_koma','ensen_nm', 'eki_nm'
    ];
    protected $fulltext_fields;
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

    public function setKenCd($kenCd) {
        $this->ken_cd = $kenCd;
    }

    public function setEnsenEkiCd($ensen_eki_cd)
    {
        $this->ensen_eki_cd = $ensen_eki_cd;
    }
    
    public function setPerPage($per_page)
    {
        $this->per_page = $per_page;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function setFulltext($fulltext)
    {
        $this->fulltext = $fulltext;
    }

    public function setOrderBy($order_by)
    {
        $this->order_by = $order_by;
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

    public function setFacetsOnly() {
    	$this->facets_only = 1;
    	$this->data_model = null;
        $this->display_model = null;
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

    // #3015 remove
    /* public function setDislayModel() {
        $this->display_model = [
            'id','kanrihito','shikikin',
            'kakaku','hoshokin','reikin',
            'madori','tatemono_ms','shumoku_nm',
            'csite_chikunengetsu','csite_bukken_title',
            'csite_bukken_shumoku_cd','toho','csite_kotsus',
            'csite_shozaichi','csite_kakaku',
            'csite_kanrihito','csite_shikikin',
            'csite_hoshokin','csite_reikin',
            'tatemono_ms','tatemono_tsubo_su',
            'csite_tsubo_tanka','tsubo_tanka',
            'csite_kaidate_kai','tochi_ms',
            'tochi_tsubo_su','kenpei_ritsu',
            'yoseki_ritsu','tatemono_kozo',
            'csite_shido_futan_ms',
            'new_mark_fl_for_c','csite_panorama_kokai_fl',
            'shinchiku_chuko_cd','csite_shashin_jujitsu_fl',
            'niji_kokoku_jido_kokai_fl','csite_images_madori_last',
        ];
    }*/

    public function fieldHighlightLenght() {
        $field_highlight_length = [
            'staff_comment.highlight_length' => 40,
            'ippan_message_shosai.highlight_length' => 40,
        ];
        foreach ($field_highlight_length as $key=>$val) {
            $this->$key = $val;
        }
    }

    public function setDislayModel() {
        $this->display_model = array_diff($this->display_model, array("staff_comment"));
    }

	public function setBukkenNo($bukken_no) {
        $this->bukken_no = $bukken_no;
    }

    public function setKokaiPublishType() {
        $this->manryo_kokai_status_cd = '03';
    }

    public function setConditionAllSearch($or) {
        $this->or[] = $or;
    }
    
}