<?php
namespace Library\Custom\Model\Estate\Search\Second\Chikunensu1;

class Kodate extends Chikunensu1Abstract {
	
	static protected $_instance;
	
	protected $_list = [
		'0'=>'指定なし',
		'10'=>'新築',
		'20'=>'3年以内',
		'30'=>'5年以内',
		'40'=>'10年以内',
		'50'=>'20年以内',
			];
}