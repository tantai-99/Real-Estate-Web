<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Config\Abstract;

class EnabledItem {
	
	static protected $_instance;
	
	protected $_list = [];
	protected $_typeIndex;
	protected $_types;
	
	public function isEnable($type, $categoryId, $itemId) {
		if (!isset($this->_typeIndex[$type])) {
			return false;
		}
		$typeIndex = $this->_typeIndex[$type];
		return !empty($this->_list[$categoryId][$itemId][$typeIndex]);
	}
	
	public function getEnableTypes($categoryId, $itemId) {
		if (!isset($this->_list[$categoryId][$itemId])) {
			return [];
		}
		
		$result = [];
		foreach ($this->_list[$categoryId][$itemId] as $index => $enabled) {
			if ($enabled) {
				$result[] = $this->_types[$index];
			}
		}
		return $result;
	}
	
	public function pickCategoryItemId($categoryIds) {
		$result = [];
		foreach ($this->_list as $categoryId => $items) {
		    if (!in_array($categoryId, $categoryIds)) {
		        continue;
		    }
			$result[$categoryId] = array_keys($items);
		}
		return $result;
	}
	
	public function getAllCategoryItemId() {
		$result = [];
		foreach ($this->_list as $categoryId => $items) {
			$result[$categoryId] = array_keys($items);
		}
		return $result;
	}
	
	static public function getInstance() {
		if (!static::$_instance) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}
}