<?php
namespace Library\Custom\Model\Estate;

class SecondEstateEnabledList extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
		1=>'設定する',
		0=>'設定しない',
	];
}