<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Item;

class Factory {

	protected $_enabledItemConfig;
	
	protected $_itemTypeConfig;
	protected $_itemLabelConfig;
	
	protected $searchFilter;
	protected $_itemOptionsFactory;
	
	public function __construct($enabledItemConfig, $itemTypeConfig, $itemLabelConfig, $itemOptionsFactory) {
		$this->_enabledItemConfig  = $enabledItemConfig;
		$this->_itemLabelConfig    = $itemLabelConfig;
		$this->_itemTypeConfig     = $itemTypeConfig;
		$this->_itemOptionsFactory = $itemOptionsFactory;
	}
	
	/**
	 * 
	 * @param int $estateType
	 * @param string $categoryId
	 * @param string $itemId
	 * @param string|array $value
	 */
	public function create($estateType, $categoryId, $itemId, $value = null) {
		
		if (!$this->_enabledItemConfig->isEnable($estateType, $categoryId, $itemId)) {
			return null;
		}
		
		$itemType = $this->_itemTypeConfig->getType($categoryId, $itemId);
		$itemLabel = $this->_itemLabelConfig->getLabel($categoryId, $itemId, $estateType);
		$itemOptions = $this->_itemOptionsFactory->get($estateType, $categoryId, $itemId);
		if ($itemType == 'list') {
			$itemType = 'itemList';
		}
		$className = 'Library\Custom\Estate\Setting\SearchFilter\Item\\'.ucfirst($itemType);
		
		$item = new $className($estateType, $categoryId, $itemId, $itemLabel, $itemOptions);
		if ($value !== null && false === $item->parse($value)) {
			return null;
		}
		return $item;
	}
	
	public function createMulti($estateType, $categoryId, $itemIds) {
		$items = [];
		foreach ($itemIds as $itemId) {
			if ($item = $this->create($estateType, $categoryId, $itemId)) {
				if ($this->searchFilter) {
					foreach($this->searchFilter->pickDesiredCategories() as $category) {
						if ($category->category_id == 'shumoku') {
							$i = 0;
							foreach($category->items as $ite) {
								if ((int)$ite->item_id != (int)$itemId) {
									$i++;
								}
							}
							if ($i == count($category->items)) {
								$item->item_flg = true;
							}
						}
					}
				}
				$items[] = $item;
			}
		}
		return $items;
	}

	public function setSearchFilter($searchFilter) {
		$this->searchFilter = $searchFilter;
	}
}