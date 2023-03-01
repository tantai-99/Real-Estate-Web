<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Category;

class Category {
	
	public $category_id;
	public $items;
	protected $_itemMap;
	
	protected $_estateType;
	
	protected $_label;
	protected $_description;
	
	/**
	 * 
	 * @var Library\Custom\Estate\Setting\SearchFilter\Item\Factory
	 */
	protected $_itemFactory;
	
	public function __construct($estateType, $categoryId, $categoryLabel, $categoryDescription, $itemFactory, $items = null) {
		$this->_estateType  = $estateType;
		$this->category_id  = $categoryId;
		$this->_label       = $categoryLabel;
		$this->_description = $categoryDescription;
		$this->_itemFactory = $itemFactory;
		
		$this->init();
		
		if (!empty($items)) {
			$this->parse($items);
		}
	}
	
	public function init() {
		$this->items = [];
		$this->_itemMap = [];
	}
	
	public function parse($items) {
		if (!is_array($items)) {
			return false;
		}
		
		foreach ($items as $item) {
			if (!isset($item['item_id']) || isEmptyKey($item, 'item_value')) {
				continue;
			}
			if ($itemObject = $this->_itemFactory->create($this->_estateType, $this->category_id, $item['item_id'], $item['item_value'])) {
				$this->items[] = $itemObject;
				$this->_itemMap[$itemObject->item_id] = $itemObject;
			}
		}
		return !!count($this->items);
	}
	
	public function updateItemMap() {
		$this->_itemMap = [];
		foreach ($this->items as $item) {
			$this->_itemMap[ $item->item_id ] = $item;
		}
	}
	
	public function getLabel() {
		return $this->_label;
	}
	
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * マスタとして利用できるようにする
	 */
	public function asMaster() {
		$this->label = $this->getLabel();
		$this->description = $this->getDescription();
		foreach ($this->items as $item) {
			$item->asMaster();
		}
	}
	
	/**
	 * @param string $itemId
	 * @return Library\Custom\Estate\Setting\SearchFilter\Item\ItemAbstract
	 */
	public function getItem($itemId) {
		return isset($this->_itemMap[$itemId]) ? $this->_itemMap[$itemId] : null;
	}
	
	/**
	 * ユーザサイトからの検索条件を受け取る
	 * @param array $values
	 * @return Library\Custom\Estate\Setting\SearchFilter\Category\Category
	 */
	public function setValues($values) {
	    if (!is_array($values)) {
	        return $this;
	    }
	    
	    foreach ($values as $itemId => $value) {
	        $item = $this->getItem($itemId);
	        if (!$item) {
	            continue;
	        }
	        $item->setValue($value);
	    }
	    return $this;
	}
	
	public function unsetValueToNotLoadedItems() {
		foreach ($this->items as $item) {
			if ($item->isLoaded()) {
				continue;
			}
			$item->unsetValue();
		}
	}
	
	public function getItemsWithoutLoaded() {
		$items = [];
		foreach ($this->items as $item) {
			if ($item->isLoaded()) {
				continue;
			}
			$items[] = $item;
		}
		return $items;
	}
}