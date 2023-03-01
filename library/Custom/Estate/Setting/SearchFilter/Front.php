<?php
namespace Library\Custom\Estate\Setting\SearchFilter;

use Library\Custom\Estate\Setting\SearchFilter\Config\Front\CategoryLabel;
use Library\Custom\Estate\Setting\SearchFilter\Config\Front\CategoryDescription;
use Library\Custom\Estate\Setting\SearchFilter\Config\Front\EnabledFrontItem;
use Library\Custom\Estate\Setting\SearchFilter\Config\Front\ItemType;
use Library\Custom\Estate\Setting\SearchFilter\Config\Front\ItemLabel;
use Library\Custom\Model\Estate\Search\Special;
use Library\Custom\Estate\Setting\SearchFilter\Item;
use Library\Custom\Estate\Setting\SearchFilter\Category;

class Front extends SearchFilterAbstract {

	public $categories;
	public function __construct() {
		$categoryLabelConfig = CategoryLabel::getInstance();
		$categoryDescriptionConfig = CategoryDescription::getInstance();
		$enabledItemConfig   = EnabledFrontItem::getInstance();
		$itemTypeConfig      = ItemType::getInstance();
		$itemLabelConfig     = ItemLabel::getInstance();
		$itemOptionsFactory  = new Special\Factory();
		
		$itemFactory = new Item\Factory(
			$enabledItemConfig, $itemTypeConfig, $itemLabelConfig, $itemOptionsFactory);
		
		$categoryFactory = new Category\Factory(
			$categoryLabelConfig, $categoryDescriptionConfig, $itemFactory);
		
		$this->_enabledItemConfig = $enabledItemConfig;
		$this->_categoryFactory   = $categoryFactory;
		$this->_itemFactory       = $itemFactory;
		
		parent::__construct();
	}
}