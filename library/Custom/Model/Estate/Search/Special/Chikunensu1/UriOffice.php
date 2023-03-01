<?php
namespace Library\Custom\Model\Estate\Search\Special\Chikunensu1;
use Library\Custom\Model\Estate\AbstractList;

class UriOffice extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
'0'=>'指定なし',
'10'=>'1年以内',
'20'=>'3年以内',
'30'=>'5年以内',
'40'=>'10年以内',
'50'=>'15年以内',
'60'=>'20年以内',
'70'=>'25年以内',
'80'=>'30年以内',
'90'=>'35年以内',
'100'=>'40年以内',
'110'=>'新築を除く',
			];
}