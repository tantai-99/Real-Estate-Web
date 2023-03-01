<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Item;

class Flag extends ItemAbstract {
	
	protected $_type = 'flag';
	
	public function init() {
		$this->_setParsedValue(0);
	}
	
	public function parse($value) {
		$this->_setParsedValue($value ? 1 : 0);
	}
	
	public function setValue($value) {
	    $this->item_value = (int)$value;
	}
	
	public function unsetValue() {
		$this->item_value = 0;
	}
}