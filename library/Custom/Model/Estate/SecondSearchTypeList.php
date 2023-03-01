<?php
namespace Library\Custom\Model\Estate;

class SecondSearchTypeList extends SearchTypeList {
	
	static protected $_instance;
	
	protected $_list = [
		self::TYPE_AREA=>'市区郡を対象にする',
		self::TYPE_ENSEN=>'沿線・駅を対象にする',
	];
}