<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Item;

class Multi extends ItemList {
	
	protected $_type = 'multi';
	
	/**
	 * @var array
	 */
	public $item_value;
	
	public function init() {
		$this->_setParsedValue([]);
	}
	
	public function parse($value) {
		if (!is_array($value)) {
			return false;
		}
		$options = $this->getOptions();
		$values = [];
		foreach ($value as $val) {
			if (isset($options[$val])) {
				$values[] = $val;
			}
		}
		if (!$values) {
			return false;
		}
		
		$this->_setParsedValue($values);
	}
	
	public function setValue($value) {
	    if (is_array($value)) {
	        $this->item_value = $value;
	    }
	}
	
	public function unsetValue() {
		$this->item_value = [];
	}
}