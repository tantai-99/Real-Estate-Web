<?php
namespace Library\Custom\Model\Estate;

class SpecialPublishEstateList extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
		'jisha_bukken'  => '自社物件',
		'niji_kokoku'	=> '2次広告物件',
		'niji_kokoku_jido_kokai'	=> '2次広告自動公開物件',
		'only_er_enabled' => '『自社ホームページ』のみ公開の物件だけ表示する',
	];
}