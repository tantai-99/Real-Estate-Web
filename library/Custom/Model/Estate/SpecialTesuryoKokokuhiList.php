<?php
namespace Library\Custom\Model\Estate;

class SpecialTesuryoKokokuhiList extends AbstractList {
	
	static protected $_instance;
	
/*
	protected $_list = [
		'only_er_enabled' => 'エンジンレンタルのみ公開の物件だけ表示する',
		'second_estate_enabled' => '2次広告自動公開の物件を含める',
		'end_muke_enabled' => 'エンド向け仲介手数料不要の物件だけ表示する',
		'only_second' => '2次広告物件のみ表示',
		'exclude_second' => '2次広告物件を除く',
	];
*/
	protected $_list = [
		'end_muke_enabled' => 'エンド向け仲介手数料不要の物件だけ表示する',
		'tesuryo_ari_nomi' => '手数料ありの物件だけ表示する（分かれを除く）',
		'tesuryo_wakare_komi' => '手数料ありの物件だけ表示する（分かれを含める）',
		'kokokuhi_joken_ari' => '広告費ありの物件だけ表示する',
	];
}