<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Item;

class ItemAbstract {
	
	protected $_type;
	// loadEnablesでロードされたかどうか
	protected $_is_loaded = false;
	
	protected $_estateType;
	protected $_categoryId;
	
	protected $_label;
	
	public $item_id;
	public $item_value;
	public $item_flg = false;
	
	protected $_parsed_value;
	
	/**
	 * @var ibrary\Custom\Model\Estate\AbstractList
	 */
	protected $_options;
	
	public function __construct($estateType, $categoryId, $itemId, $label, $options = null) {
		$this->_label = $label;
		$this->_options = $options;
		
		$this->_estateType = $estateType;
		$this->_categoryId = $categoryId;
		$this->item_id = $itemId;
		$this->init();
	}
	
	public function setIsLoaded($bool=true) {
	    $this->_is_loaded = $bool;
	}
	
	public function isLoaded() {
	    return $this->_is_loaded;
	}
	
	public function getLabel() {
		return $this->_label;
	}
	
	public function getType() {
	    return $this->_type;
	}
	
	/**
	 * マスタとして利用できるようにする
	 */
	public function asMaster() {
		$this->type  = $this->_type;
		$this->label = $this->getLabel();
	}
	
	public function init() {
	}
	
	public function parse($value) {
		
	}
	
	public function getParsedValue() {
		return $this->_parsed_value;
	}
	
	protected function _setParsedValue($value) {
		$this->item_value = $value;
		$this->_parsed_value = $value;
	}
	
	public function setValue($value) {
	    $this->item_value = $value;
	}
	
	public function unsetValue() {
		$this->item_value = null;
	}
}