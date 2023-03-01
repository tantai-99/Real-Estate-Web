<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class ItemType {
	
	static protected $_instance;
	
	protected $_list = [];
	
	public function getType($category, $itemId) {
		return isset($this->_list[$category][$itemId]) ?
						$this->_list[$category][$itemId]:
						'flag';
	}
	
	static public function getInstance() {
		if (!static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
}