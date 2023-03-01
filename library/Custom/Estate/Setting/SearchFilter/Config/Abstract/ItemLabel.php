<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class ItemLabel {
	
	static protected $_instance;
	
	protected $_list = [];
	
	public function getLabel($category, $itemId, $type = null) {
		if (!isset($this->_list[$category][$itemId])) {
			return null;
		}
		$label = $this->_list[$category][$itemId];
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