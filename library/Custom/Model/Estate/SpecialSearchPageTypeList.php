<?php
namespace Library\Custom\Model\Estate;

class SpecialSearchPageTypeList extends AbstractList {
	
	static protected $_instance;
	
	protected $_list = [
		1 => '検索画面あり',
		0 => '検索画面なし'
	];
}