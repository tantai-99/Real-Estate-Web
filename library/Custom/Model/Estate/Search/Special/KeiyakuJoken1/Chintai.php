<?php
namespace Library\Custom\Model\Estate\Search\Special\KeiyakuJoken1;
use Library\Custom\Model\Estate\AbstractList;

class Chintai extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
'10'=>'定期借家除く',
'0'=>'定期借家含む',
'30'=>'定期借家のみ',
'40'=>'短期貸し物件',
	];
}