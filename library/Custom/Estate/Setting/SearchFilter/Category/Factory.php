<?php
namespace Library\Custom\Estate\Setting\SearchFilter\Category;
use Library\Custom\Estate\Setting\SearchFilter\Category\Category;

class Factory {
	
	/**
	 * 
	 * @var Library\Custom\Estate\Setting\SearchFilter\Item\Factory
	 */
	protected $_itemFactory;
	protected $_categoryLabelConfig;
	protected $_categoryDescriptionConfig;
	
	
	public function __construct($categoryLabelConfig, $categoryDescriptionConfig, $itemFactory) {
		$this->_itemFactory = $itemFactory;
		$this->_categoryLabelConfig = $categoryLabelConfig;
		$this->_categoryDescriptionConfig = $categoryDescriptionConfig;
	}
	
	/**
	 * 
	 * @param int $estateType
	 * @param string $categoryId
	 * @param array $items
	 * @return Library\Custom\Estate\Setting\SearchFilter\Category\Category
	 */
	public function create($estateType, $categoryId, $items = null) {
		$itemName = pascalize($categoryId);
		if ($itemName === 'Factory' || strpos($itemName, 'Abstract') === 0) {
			return null;
		}
		$categoryLabel = $this->_categoryLabelConfig->getLabel($categoryId, $estateType);
		$categoryDescription = $this->_categoryDescriptionConfig->getDescription($categoryId, $estateType);
		$category = new Category($estateType, $categoryId, $categoryLabel, $categoryDescription, $this->_itemFactory);
		if ($items !== null && false === $category->parse($items)) {
			return null;
		}
		return $category;
	}
}