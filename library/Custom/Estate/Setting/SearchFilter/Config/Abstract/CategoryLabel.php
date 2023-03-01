<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class CategoryLabel {
	
	static protected $_instance;
	
	protected $_list = [];
	
	public function getLabel($category, $type = null) {
		if (!isset($this->_list[$category])) {
			return null;
		}
		$label = $this->_list[$category];
		if (is_array($label)) {
			return isset($label[$type]) ? $label[$type] : $label['default'];
		}
		else {
			return $label;
		}
	}
	
	static public function getInstance() {
		if (!static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
}