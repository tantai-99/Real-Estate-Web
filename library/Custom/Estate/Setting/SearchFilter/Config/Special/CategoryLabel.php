<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Special;
use Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;
use Library\Custom\Model\Estate\TypeList;

class CategoryLabel extends Abstract\CategoryLabel {
	
	static protected $_instance;
	
	public function __construct() {
		
		$labels['shumoku'] = '物件種目';
		
		$labels['kakaku']=[
			'default'=>'賃料',
			TypeList::TYPE_URI_OFFICE=>'価格',
			TypeList::TYPE_KODATE=>'価格',
			TypeList::TYPE_MANSION=>'価格',
			TypeList::TYPE_URI_OTHER=>'価格',
			TypeList::TYPE_URI_TENPO=>'価格',
			TypeList::TYPE_URI_TOCHI=>'価格',
            TypeList::COMPOSITETYPE_BAIBAI_KYOJU_1=>'価格',
            TypeList::COMPOSITETYPE_BAIBAI_KYOJU_2=>'価格',
            TypeList::COMPOSITETYPE_BAIBAI_JIGYO_1=>'価格',
            TypeList::COMPOSITETYPE_BAIBAI_JIGYO_2=>'価格',
		];
		
		$labels['rimawari'] = '利回り';
		$labels['keiyaku_joken']='契約条件';
		
		$labels['madori']='間取り';
		$labels['menseki']='面積';
		
		$labels['tatemono_kozo']='建物構造';
		
		$labels['saiteki_yoto']='最適用途';
		$labels['eki_toho_fun']='駅からの徒歩';
		$labels['chikunensu']=[
			'default'=>'築年数',
			TypeList::TYPE_MANSION=>'完成時期（築年数）'
		];
		$labels['reform_renovation']='リフォーム・リノベーション';
		$labels['reformable_parts']='リフォーム可能箇所';

		$labels['open_room']='オープンルーム・モデルルーム';
		$labels['open_house']='オープンハウス・モデルハウス';
		$labels['genchi_hanbaikai']='現地販売会';
		$labels['joho_kokai']='情報公開日';
		
		$labels['pro_comment']='アピール';
		$labels['image']='画像';
		
		$labels['kitchen']='キッチン';
		
		$labels['bath_toilet']='バス・トイレ';
		
		$labels['reidanbo']='冷暖房';
		
		$labels['shuno']='収納';
		
		$labels['tv_tsusin']='テレビ・通信';
		
		$labels['security']='セキュリティ';
		
		$labels['ichi']='位置';
		
		$labels['joken']='条件';
		
		$labels['genkyo']='現況';
		$labels['kyouyu_shisetsu']='共用施設';
		
		$labels['setsubi_kinou']='設備・機能';
		
		$labels['tokucho']='特徴';
		
		$labels['koho_kozo']='工法・構造';
		
		$labels['torihiki_taiyo']='取引態様';
		
		$labels['other']='その他';
		
		$this->_list = $labels;
	}
}