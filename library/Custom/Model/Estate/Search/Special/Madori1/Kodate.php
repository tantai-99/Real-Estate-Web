<?php
namespace Library\Custom\Model\Estate\Search\Special\Madori1;
use Library\Custom\Model\Estate\AbstractList;

class Kodate extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
'10'=>'1LDK以下',
'20'=>'2K',
'30'=>'2DK',
'40'=>'2LDK',
'50'=>'3K',
'60'=>'3DK',
'70'=>'3LDK',
'80'=>'4K',
'90'=>'4DK',
'100'=>'4LDK',
'110'=>'5K',
'120'=>'5DK',
'130'=>'5LDK以上',
// 削除
// '140'=>'間取り未定の物件を含む',
	];
}