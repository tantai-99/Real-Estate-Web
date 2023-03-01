<?php
namespace Library\Custom\Estate\Setting\SearchFilter;

use Library\Custom\Estate\Setting\SearchFilter\Config;
use Library\Custom\Model\Estate\Search\Special\Factory;
use Library\Custom\Estate\Setting\SearchFilter\Item;
use Library\Custom\Estate\Setting\SearchFilter\Category;
use Library\Custom\Estate\Setting\SearchFilter\Front;

class Special extends SearchFilterAbstract {

	public $categories;
	public function __construct() {
		$categoryLabelConfig = Config\Special\CategoryLabel::getInstance();
		$categoryDescriptionConfig = Config\Special\CategoryDescription::getInstance();
		$enabledItemConfig   = Config\Special\EnabledItem::getInstance();
		$itemTypeConfig      = Config\Special\ItemType::getInstance();
		$itemLabelConfig     = Config\Special\ItemLabel::getInstance();
		$itemOptionsFactory  = new Factory();

		$itemFactory = new Item\Factory(
			$enabledItemConfig, $itemTypeConfig, $itemLabelConfig, $itemOptionsFactory);
		$categoryFactory = new Category\Factory(
			$categoryLabelConfig, $categoryDescriptionConfig, $itemFactory);
		
		$this->_enabledItemConfig = $enabledItemConfig;
		$this->_categoryFactory   = $categoryFactory;
		$this->_itemFactory       = $itemFactory;
		$this->_itemFactory->setSearchFilter($this);
		parent::__construct();
	}
	
	/**
	 * 
	 * @param string $estateType 物件種目
	 */
	public function toFrontSearchFilter($estateType) {
	    $searchFilter = new Front();
	    $json = json_decode(json_encode($this->categories), true);
	    $searchFilter->parse($estateType, ['categories'=>$json]);
	    $searchFilter->setParsed($this->isParsed());
	    return $searchFilter;
	}
    
    public function setSearchFilterInvidial($searchFilter) {
        $i = 0;
        foreach ($searchFilter->categories as $categories) {
            if ($categories->category_id != 'shumoku') {
                unset($searchFilter->categories[$i]);
            }
            $i++;
        }
        return $searchFilter->categories;
    }

    public function setSearchFilterInvidialAfterSearch($searchFilter, $params) {
        $i = 0;
        foreach ($searchFilter->categories as $categories) {
            $isUnsetTarget = true;
            foreach ($params as $key => $param) {
                if ($categories->category_id == $key) {
                    $isUnsetTarget = false;
                }
            }
            if ($isUnsetTarget && $categories->category_id != 'shumoku') {
                unset($searchFilter->categories[$i]);
            }
            $i++;
        }
        return $searchFilter->categories;
    }
}