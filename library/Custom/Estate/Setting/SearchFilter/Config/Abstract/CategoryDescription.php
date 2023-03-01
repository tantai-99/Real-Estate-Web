<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class CategoryDescription {
	
	static protected $_instance;
	
	protected $_list = [];
	
	public function getDescription($category, $type = null) {
		if (!isset($this->_list[$category])) {
			return null;
		}
		$desc = $this->_list[$category];
		if (is_array($desc)) {
			return isset($desc[$type]) ? $desc[$type] : $desc['default'];
		}
		else {
			return $desc;
		}
	}
	
	static public function getInstance() {
		if (!static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
}