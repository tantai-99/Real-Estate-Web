<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Item;

class ItemList extends ItemAbstract {
	
	protected $_type = 'list';
	
	public function init() {
		$this->_setParsedValue(null);
	}
	
	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->_options->getAll();
	}
	
	public function getOptionModel() {
	    return $this->_options;
	}
	
	public function parse($value) {
		$options = $this->getOptions();
		if (!isset($options[$value])) {
			return false;
		}
		
		$this->_setParsedValue($value);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Library\Custom\Estate\Setting\SearchFilter\Item\ItemAbstract::asMaster()
	 */
	public function asMaster() {
		parent::asMaster();
		$options = [];
		foreach ($this->getOptions() as $value => $label) {
			$options[] = [
				'value' => $value,
				'label' => $label,
			];
		}
		$this->options = $options;
	}
	
	public function setValue($value) {
	    $this->item_value = (string)$value;
	}
	
	public function unsetValue() {
		$this->item_value = null;
	}
}