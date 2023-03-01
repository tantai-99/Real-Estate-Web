<?php
namespace Library\Custom\Model\Estate;

class KomaSortOptionList extends AbstractList {
	
	static protected $_instance;
	
	const SORT_RANDOM = 1;
	const SORT_PRICE = 2;
	const SORT_TIME = 3;
	
	protected $_key_consts = [];
	
	protected $_list = [
		self::SORT_RANDOM	=>'ランダム',
		self::SORT_PRICE	=>'価格（安い）',
		self::SORT_TIME		=>'新着',
	];
}