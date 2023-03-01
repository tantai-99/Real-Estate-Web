<?php
namespace Library\Custom\Model\Estate\Search\Second\Chikunensu1;
use Library\Custom\Model\Estate\AbstractList;

class KasiOffice extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
		'0'=>'指定なし',
		'10'=>'1年以内',
		'20'=>'3年以内',
		'30'=>'5年以内',
		'40'=>'10年以内',
		'50'=>'20年以内',
			];
}