<?php 
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate;
use Modules\V1api\Services;

class HighlightItemList
{
	static protected $_instance;
	protected $_list = array(
        'csite_kotsus_for_highlight' => '交通',
		'shumoku_nm'                 => '物件種目',
		'ippan_message_shosai'       => 'アピールポイント',
		'madori'                     => '間取り',
		'madori_uchiwake'            => '間取り内訳',
		'sonota_ichijikin'           => 'その他一時金',
		'ijihito'                    => '維持費等',
		'hokento'                    => '保険等',
		'credit_kessai'              => 'クレジットカード決済',
		'csite_tatemono_nm'          => '建物名・部屋番号',
		'csite_tokkis'               => '特記事項',
		'csite_setsubis'             => '設備',
		'shuyo_saikomen'             => '主要採光面',
		'inspection'                 => 'インスペクション',
		'kashi_hoken_hosho'          => '瑕疵保険・保証',
		'csite_bikos'                => '備考',
		'onsen_hikikomi_jokyo'       => '温泉引き込み状況',
		'onsen_riyo_keitai'          => '温泉利用形態',
		'onsen_hiyo_to'              => '温泉費用等',
		'csite_kaidate_kai'          => '階建/階',
		'tatemono_kozo'              => '建物構造',
		'reform'                     => 'リフォーム履歴',
		'renovation'                 => 'リノベーション履歴',
		'chushajo'                   => '駐車場',
		'churinjo'                   => '駐輪場',
		'bike_okiba'                 => 'バイク置き場',
		'chisei'                     => '地勢',
		'sokosu'                     => '総戸数',
		'setsudo_jokyo'              => '接道状況',
		'setback'                    => 'セットバック',
		'pet'                        => 'ペット',
		'kanri_keitai'               => '管理形態／管理員の勤務形態',
		'csite_jokento'              => '条件等',
		'hikiwatashi'                => '引渡可能時期',
		'koshin_ryo'                 => '更新料',
		'images.caption'             => '画像',
		'shuhen_kankyos.caption'     => '周辺環境 画像',
		'shuhen_kankyos.nm'          => '周辺環境',
		'shuhen_kankyos.shubetsu_nm' => '周辺施設',
		'csite_panorama_kokai_fl'    => ''
    );
    
    /**
     * get highlight
     * @param int $key
     * @return string 
     */
    public function get($key) {
    	return isset($this->_list[$key])?$this->_list[$key]:null;
    }
    /**
     * @return HighlightItemList
     */
    static public function getInstance() {
        if (!static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
    
}
?>