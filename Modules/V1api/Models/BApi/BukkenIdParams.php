<?php
namespace Modules\V1api\Models\BApi;

class BukkenIdParams extends AbstractParams
{

    // 物件APIに接続するパラメータは、
    // パラメータ名をprotected変数名として定義。
    // 値はすべて文字列か配列とする。
	protected $group_id;
    protected $kaiin_link_no;
    protected $csite_bukken_shumoku_cd;

    protected $data_model = [
        'shozaichi_cd1', 'shozaichi_cd2', // ATHOME_HP_DEV-5001: shozaichi_cd2 追加
        'setsubi_cd', 'images',
        'aux_images', // NHP-5163 物件詳細画面の画像17〜40枚目
        'genkyo_nm', 'ippan_kokai_message',
        'tatemono_nm_hyoji_fl',
//     		'heya_no_hihyoji_fl',
//        'tatemono_nm', 'heya_no',
        'chizu_kensaku_fuka_fl',
        'shuhen_kankyos', 'ido', 'keido',
        'kaiin_no',
        'saiteki_yoto_nm',
        'yoto_chiiki_nm',
    	'hikiwatashi_joken_nm',
        'kenchiku_joken_tsuki_fl', // 建築条件付き
        'tochi_kenri_nm',
    	'toshi_keikaku_nm',
    	'chimoku_nm',
    	'kokudoho_nm',
    	'kenchiku_kakunin_no',
    	'chikugo_minyukyo_fl',
        'panorama_contents_id',
    	'panorama',
        'panorama_vr_image_fl',   // パノラマ種別判定用(NHP-4591)

    	'jishatasha_cd', // 自社他社コード：２次広告判定用
        'reform_renovation_ka_fl',
    	/*
    	 * おすすめポイント表示用項目　↓ここから
    	 */
    	'kanri_hoshiki_cd',
    	'pet_sodan_ari_fl',
    	'free_rent_ari_fl',
    	'maisonnet_ari_fl',
    	//'reform_ari_fl',
        //'renovation_fl',
    	'resort_fl',
    	'bike_okiba_cd',
    	'churinjo_cd',
    	/*
    	 * おすすめポイント表示用項目　↑ここまで
    	 */
        //項目追加対応 ↓
        'onsen_hikikomi_jokyo_cd',
        'onsen_hikikomi_zumi_cd',
        'onsen_riyo_keitai_cd',
        'onsen_hiyo_to',
        'shuyo_saikomen_nm',
        'shuyo_saikomen_cd',
        //項目追加対応 ↑

        // 価格非公開フラグ
        'kakaku_hikokai_fl',

        // 物件反響メールのバス情報用
        'kotsus',

        //項目追加(2018/9/20)
        'kashi_hoken_kijun_tekigo_fl',
        'kashi_hosho_fudosan_dokuji_cd',
        'inspection_cd',
        'choki_yuryo_jutaku_zokaichiku_fl',
        'flat35_tekigo_fl',
        'flat35s_tekigo_fl',
        'taishin_kijun_tekigo_fl',
        'hotekigo_jokyo_chosa_fl',
        'kenchikushi_inspection_fl',

        //項目追加(2019/9/12) : リフォーム
        'reform_renovation_mizumawari_cd',
        'reform_renovation_mizumawari_sonota',
        'reform_renovation_naiso_cd',
        'reform_renovation_naiso_sonota',
        'reform_renovation_sonota',
        // 項目追加(2019/10/02)
        'version_no',
        'teiki_shakka_fl',
        'owner_change_fl',
    ];


    // 物件APIパラメータに使用されない
    // 変則的に使用される変数はprivateで定義。
    private $id;

    public function setId($bukken_id)
    {
        $this->id = $bukken_id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $kaiin_link_no
     */
    public function setKaiinLinkNo ($kaiin_link_no)
    {
    	$this->kaiin_link_no = $kaiin_link_no;
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

    /**
     * 物件種目コード系
     */
    public function setCsiteBukkenShumokuCd($csite_bukken_shumoku_cd)
    {
        $this->csite_bukken_shumoku_cd = $csite_bukken_shumoku_cd;
    }

    public function getCsiteBukkenShumokuCd()
    {
        return $this->csite_bukken_shumoku_cd;
    }

    protected $display_model = [
        'id', 'kaiin_link_no',
        'csite_bukken_shumoku_cd', 'kodawari_joken_cd',
        'kotsus', 'shikugun_nm', 'ken_nm',

// ---- common ----
'shumoku_nm',
'csite_shozaichi',
'csite_ken_shozaichi',
'kakaku',
'kanrihi',
'kanrihito',
'shikikin',
'hoshokin',
'reikin',
'shikibiki',
'tatemono_tsubo_su',
'tochi_tsubo_su',
'madori',
'csite_chikunengetsu',
'csite_kaidate_kai',
'kenpei_ritsu',
'yoseki_ritsu',
'csite_shido_futan_ms',
'csite_panorama_kokai_fl',  // パノラマの表示条件を変更(panorama_movie_ari_fl → csite_panorama_kokai_fl)
'csite_bukken_title',

'panorama_contents_cd',     // パノラマ種別判定用(NHP-4591)
'panorama_webvr_fl',        // パノラマ種別判定用(NHP-4591)

'csite_kakaku',
'csite_kanrihito',
'csite_tsubo_tanka',
'csite_tsubo_tanka_manen',
'csite_shikikin',
'csite_hoshokin',
'csite_reikin',
'csite_shikibiki',
'csite_hoshokin_shokyaku',
'csite_keiyaku_kikan',
'csite_koshin_ryo',

'joi_shumoku_cd',


// ---- list ----
'csite_kotsus',
'toho',
'tsubo_tanka',
'tatemono_kozo',
'tatemono_ms',
'tochi_ms',
'tochi_tsubo_su',
'tatemono_tsubo_su',
'tsubo_tanka',
'new_mark_fl_for_c',
'csite_madorizu_mark_fl',
'csite_shashin_jujitsu_fl',
'bukken_no',
// ---- detail ----
'csite_kotsus',
'hoshokin_shokyaku',
'sonota_ichijikin',
'csite_kagi_kokandaito',
'hokento',
'heibei_tanka',
'tsubo_tanka_manen',
'shuzen_tsumitatekin',
'shakuchi_kikan',
'matching_level_cd',
//'csite_keiyaku_kikan',
'chidai',
'kenrikin',
'ijihito',
'zosaku_joto',
'madori_uchiwake',
'tatemono_ms',
'tochi_shikichi_ms',
'balcony',
'chushajo',
'tatemono_kozo',
'bike_okiba',
'churinjo',
'reform',
'renovation',
'reform_renovation_ka',
'chisei',
'setsudo_jokyo',
'kanri_keitai',
'csite_kanri_keitai',
'setback',
'sokosu',
'pet',
//'setsubi',
'csite_setsubis',
'tokki',
'csite_bikos',
'open_house',
'csite_keiyaku_kikan',
'csite_jokento',
'er_jokento',
'hikiwatashi',
'koshin_ryo',
'chukai_tesuryo',
//'kokai_date_c',
'csite_kokai_date',
'jikai_koshin_yotei_date',
//'panorama',
'csite_torihiki_taiyo',
'kanri_no',
'shinchiku_chuko_cd',
// 'tatemono_nm',
// 'heya_no',
'kokaichu_kokais',
'niji_kokoku_jido_kokai_fl',
'er_jisha_group_fl',
'csite_tatemono_nm',
'tatemono_nobe_ms',
'credit_kessai',
'nairankai_joho',
'ippan_message_shosai', // アピールポイント

//項目追加対応 ↓
'onsen_hikikomi_jokyo',
'onsen_riyo_keitai',
'onsen_hiyo_to',
'csite_tokkis',
'csite_osusumes',
'balcony_ms',
'shuyo_saikomen',
//項目追加対応 ↑

'inspection',        // インスペクション 2017/10追加

/**
 * 項目追加（2018/9/20）
 */
'kashi_hosho',
'kashi_hoken',
'choki_yuryo_jutaku_nintei_tsuchisho',
'flat35_tekigo_shomeisho',
'flat35s_tekigo_shomeisho',
'taishin_kijun_tekigo_shomeisho',
'hotekigo_jokyo_chosa_hokokusho',
'tatemono_kensa_hokokusho',

// #3015 (2019/06/12)
'csite_images_madori_last',

// #3061 (2019/06/27)
'csite_muke_kaiin_no',

'csite_images_gaikan_first',
'staff_comment',

// ATHOME_HP_DEV-5001: ken_cd 追加
'ken_cd',

    ];
}
