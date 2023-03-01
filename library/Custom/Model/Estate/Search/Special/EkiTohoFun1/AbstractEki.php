<?php
namespace Library\Custom\Model\Estate\Search\Special\EkiTohoFun1;
use Library\Custom\Model\Estate\AbstractList;

class AbstractEki extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
'0'=>'指定なし',
'10'=>'3分以内',
'20'=>'5分以内',
'30'=>'10分以内',
'40'=>'15分以内',
'50'=>'20分以内',
			];
}