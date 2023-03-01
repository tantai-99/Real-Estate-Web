<?php

namespace Library\Custom\Model\Estate;

use ReflectionClass;
class ClassList extends AbstractList {
	
	static protected $_instance;

	const RENT     = 1;
	const PURCHASE = 2;

	const CLASS_CHINTAI_KYOJU = 1;
	const CLASS_CHINTAI_JIGYO = 2;
	const CLASS_BAIBAI_KYOJU = 3;
	const CLASS_BAIBAI_JIGYO = 4;
	const CLASS_ALL = 5;

	protected $_key_consts = [];

	protected $_list = [
		1 => '居住用賃貸',
		2 => '事業用賃貸',
		3 => '居住用売買',
		4 => '事業用売買',
	];
	
	public function __construct() {
		$ref = new \ReflectionClass($this);
		$consts = $ref->getConstants();

		foreach ($consts as $const => $value) {
			if (strpos($const, 'CLASS_') === 0) {
				$this->_key_consts[$const] = $value;
			}
		}
	}

	public function getKeyConst()
	{
		return $this->_key_consts;
	}
}
