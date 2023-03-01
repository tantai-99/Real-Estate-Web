<?php
namespace Modules\V1api\Models;

use Library\Custom\Model\Estate;

class PopularItemList {
    
    static protected $_instance;
    
    protected $_list = [];
    
    public function __construct() {
        // 賃貸
        $this->_list[Estate\TypeList::TYPE_CHINTAI] =[
            // バス・トイレ別
            ['bath_toilet', '1'],
            // 追焚き機能
            ['bath_toilet', '3'],
            // シャワー付洗面化粧台
            ['bath_toilet', '14'],
            // 2階以上
            ['ichi', '1'],
            // フローリング
            ['setsubi_kinou', '1'],
            // 洗濯機置き場
            ['setsubi_kinou', '4'],
            // エアコン
            ['reidanbo', '1'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // ペット相談
            ['joken', '9'],
            // オートロック
            ['security', '1'],
        ];
        // 貸し店舗
        $this->_list[Estate\TypeList::TYPE_KASI_TENPO] =[
            // 居抜き
            ['joken', '28'],
            // 一階
            ['ichi','4'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // 飲食店可
            ['joken', '27'],
            // 即引渡し可
            ['joken', '2'],
            // エアコン
            ['reidanbo', '1'],
            // 24時間利用可
            ['joken', '30'],
        ];
        // 貸しオフィス
        $this->_list[Estate\TypeList::TYPE_KASI_OFFICE] =[
            // 一階
            ['ichi','4'],
            // 2階以上
            ['ichi', '1'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // エアコン
            ['reidanbo', '1'],
            // 男女別トイレ
            ['bath_toilet', '20'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // 光ファイバー
            ['tv_tsusin', '10'],
            // 即引渡し可
            ['joken', '2'],
            // 24時間利用可
            ['joken', '30'],
            // OAフロア
            ['koho_kozo', '19'],
        ];
        // 貸し駐車場
        $this->_list[Estate\TypeList::TYPE_PARKING] =[
            // 即引渡し可
            ['joken', '2'],
            // フリーレント
            ['joken', '12'],
        ];
        // 貸し土地
        $this->_list[Estate\TypeList::TYPE_KASI_TOCHI] =[
            // 更地
            ['genkyo', '1'],
            // 上水道
            ['setsubi_kinou', '12'],
            // 下水道
            ['setsubi_kinou', '13'],
            // 電気
            ['setsubi_kinou', '14'],
            // 都市ガス
            ['setsubi_kinou', '8'],
            // プロパンガス
            ['setsubi_kinou', '9'],
            // 側溝
            ['setsubi_kinou', '11'],
            // 角地
            ['ichi', '6'],
            // 即引渡し可
            ['joken', '2'],
            // 浄化槽
            ['kyouyu_shisetsu', '7'],
            // 汲取
            ['other', '23'],
            // 地盤調査書有
            ['other', '29'],
            // 区画整理地内
            ['other', 30],
        ];
        // 貸しその他
        $this->_list[Estate\TypeList::TYPE_KASI_OTHER] =[
            // 駐車場（近隣含む）
            ['other', '7'],
            // 即引渡し可
            ['joken', '2'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
        ];
        
        // 売りマンション
        $this->_list[Estate\TypeList::TYPE_MANSION] =[
            // 2階以上
            ['ichi', '1'],
            // 最上階
            ['ichi', '3'],
            // 角部屋
            ['ichi', '5'],
            // ペット相談
            ['joken', '9'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // 所有権
            ['joken', '24'],
            // オートロック
            ['security', '1'],
            // 洗濯機置き場
            ['setsubi_kinou', '4'],
        ];
        // 売り戸建て
        $this->_list[Estate\TypeList::TYPE_KODATE] =[
            // 所有権
            ['joken', '24'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // 駐車場2台分
            ['other', '8'],
            // 都市ガス
            ['setsubi_kinou', '8'],
            // 上水道
            ['setsubi_kinou', '12'],
            // 下水道
            ['setsubi_kinou', '13'],
            // 追焚き機能
            ['bath_toilet', '3'],
            // トイレ2箇所
            ['bath_toilet', '19'],
            // 庭
            ['other', '6'],
        ];
        // 売り土地
        $this->_list[Estate\TypeList::TYPE_URI_TOCHI] =[
            // 建築条件なし
            ['joken', '23'],
            // 所有権
            ['joken', '24'],
            // 更地
            ['genkyo', '1'],
            // 上水道
            ['setsubi_kinou', '12'],
            // 下水道
            ['setsubi_kinou', '13'],
            // 都市ガス
            ['setsubi_kinou', '8'],
            // 平坦地
            ['tokucho', '17'],
            // 角地
            ['ichi', '6'],
            // 電気
            ['setsubi_kinou', '14'],
            // 南道路
            ['ichi', '7'],
        ];
        // 売り店舗
        $this->_list[Estate\TypeList::TYPE_URI_TENPO] =[
            // 一階
            ['ichi','4'],
            // 2階以上
            ['ichi', '1'],
            // 最上階
            ['ichi', '3'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // 居抜き
            ['joken', '28'],
            // 即引渡し可
            ['joken', '2'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // エアコン
            ['reidanbo', '1'],
            // 男女別トイレ
            ['bath_toilet', '20'],
            // 給湯
            ['setsubi_kinou', '7'],
        ];
        // 売りオフィス
        $this->_list[Estate\TypeList::TYPE_URI_OFFICE] =[
            // 駐車場（近隣含む）
            ['other', '7'],
            // 一階
            ['ichi','4'],
            // 2階以上
            ['ichi', '1'],
            // 最上階
            ['ichi', '3'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // 男女別トイレ
            ['bath_toilet', '20'],
            // エアコン
            ['reidanbo', '1'],
            // 即引渡し可
            ['joken', '2'],
            // 給湯
            ['setsubi_kinou', '7'],
            // オートロック
            ['security', '1'],
        ];
        // 売りその他
        $this->_list[Estate\TypeList::TYPE_URI_OTHER] =[
            // 駐車場（近隣含む）
            ['other', '7'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // 即引渡し可
            ['joken', '2'],
            // ロフト
            ['tokucho', '10'],
            // 所有権
            ['joken', '24'],
        ];

        // 特集複合種目対応

        // 賃貸事業パターン1
        $this->_list[Estate\TypeList::COMPOSITETYPE_CHINTAI_JIGYO_1] = [
            // 一階
            ['ichi','4'],
            // 2階以上
            ['ichi', '1'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // エアコン
            ['reidanbo', '1'],
            // 男女別トイレ
            ['bath_toilet', '20'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // 光ファイバー
            ['tv_tsusin', '10'],
            // 即引渡し可
            ['joken', '2'],
            // 24時間利用可
            ['joken', '30'],
            // 居抜き
            ['joken', '28'],
        ];
        // 賃貸事業パターン2 なし
        // 賃貸事業パターン3 なし

        // 売買居住パターン1
        $this->_list[Estate\TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1] = [
            // 所有権
            ['joken', '24'],
            // 駐車場（近隣含む）
            ['other', '7'],
            // 駐車場2台分
            ['other', '8'],
            // 都市ガス
            ['setsubi_kinou', '8'],
            // 追焚き機能
            ['bath_toilet', '3'],
            // トイレ2箇所
            ['bath_toilet', '19'],
            // 庭
            ['other', '6'],
        ];
        // 売買居住パターン2 なし

        // 売買事業パターン1
        $this->_list[Estate\TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1] = [
            // 駐車場（近隣含む）
            ['other', '7'],
            // 一階
            ['ichi','4'],
            // 2階以上
            ['ichi', '1'],
            // 最上階
            ['ichi', '3'],
            // エレベーター
            ['kyouyu_shisetsu', '1'],
            // 男女別トイレ
            ['bath_toilet', '20'],
            // エアコン
            ['reidanbo', '1'],
            // 即引渡し可
            ['joken', '2'],
            // 給湯
            ['setsubi_kinou', '7'],
        ];
        // 売買事業パターン2 なし
    }
    
    /**
     * 指定の物件種目の人気のこだわり条件アイテムIDを取得する
     * @param int $estateType
     * @return array [[categoryId, itemId]...]
     */
    public function get($estateType) {
        if (is_array($estateType)) {
            $estateType = Estate\TypeList::getInstance()->getCompositeType($estateType);
        }
    	return isset($this->_list[$estateType])?$this->_list[$estateType]:null;
    }
    
    /**
     * 指定の物件種目のカテゴリーごとの人気のこだわり条件アイテムIDを取得する
     * @param int $estateType
     * @return array [categoryId=>[itemId, itemId...]...]
     */
    public function getItemIdsByCategory($estateType) {
    	$items = $this->get($estateType);
    	if (!$items) {
    		return null;
    	}
    	
    	$result = [];
    	foreach ($items as $item) {
    		$result[$item[0]][] = $item[1];
    	}
    	return $result;
    }
    
    /**
     * @return PopularItemList
     */
    static public function getInstance() {
        if (!static::$_instance) {
            static::$_instance = new static();
        }
        return static::$_instance;
    }
}