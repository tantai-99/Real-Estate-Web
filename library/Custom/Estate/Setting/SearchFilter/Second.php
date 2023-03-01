<?php
namespace Library\Custom\Estate\Setting\SearchFilter;

use Library\Custom\Estate\Setting\SearchFilter;
use Library\Custom\Model\Estate\Search\Second\Factory;
use Library\Custom\Model\Estate\TypeList;

class Second extends SearchFilterAbstract {

	public $estate_types;
	protected $_estateTypeCategoryMap;

	public function __construct() {
		$categoryLabelConfig = Config\Second\CategoryLabel::getInstance();
		$categoryDescriptionConfig = Config\Second\CategoryDescription::getInstance();
		$enabledItemConfig   = Config\Second\EnabledItem::getInstance();
		$itemTypeConfig      = Config\Second\ItemType::getInstance();
		$itemLabelConfig     = Config\Second\ItemLabel::getInstance();
		$itemOptionsFactory  = new Factory();
		
		$itemFactory = new Item\Factory(
			$enabledItemConfig, $itemTypeConfig, $itemLabelConfig, $itemOptionsFactory);
		
		$categoryFactory = new Category\Factory(
			$categoryLabelConfig, $categoryDescriptionConfig, $itemFactory);
		
		$this->_enabledItemConfig = $enabledItemConfig;
		$this->_categoryFactory   = $categoryFactory;
		$this->_itemFactory       = $itemFactory;
		
		parent::__construct();
	}

	public function init() {
		$this->estate_types = [];
		$this->_estateTypeCategoryMap = [];

		//$this->categories = [];
		//$this->_categoryMap = [];
	}

	public function updateCategoryMap() {
		$this->_estateTypeCategoryMap = [];
		foreach ($this->estate_types as $estateType) {
			$categoryMap = [];
			foreach ($estateType['categories'] as $category) {
				$categoryMap[$category->category_id] = $category;
			}
			$estateType = $estateType['estate_type'];
			$this->_estateTypeCategoryMap[$estateType]=$categoryMap;
		}
	}

	/**
	 * マスタとして利用できるようにする
	 */
	public function asMaster() {
		foreach ($this->estate_types as $estateType) {
			foreach ($estateType['categories'] as $category) {
				$category->asMaster();
			}
		}
	}


	/**
	 * 物件種目(種別)内で有効なアイテムをロードする
	 * @param int $estateType
	 */
	public function loadEnables($estateType) {
		return $this->_loadEnables($estateType);
	}

	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($estateType, $values) {
		$this->_parsed = true;

		if (!is_array($values)) {
			$values = @json_decode($values, true);
		}

		//if (!isset($values['categories']) || !is_array($values['categories'])) {
		if (!isset($values['estate_types']) || !is_array($values['estate_types'])) {
			return;
		}

		foreach ($values['estate_types'] as $estateType) {
			$categories =[];
			foreach ($estateType['categories'] as $category) {
				if (!is_array($category)) {
					continue;
				}
				if (!isset($category['category_id'])) {
					continue;
				}
				if (empty($category['items'])) {
					continue;
				}
				$categoryObject = $this->_categoryFactory->create($estateType['estate_type'], $category['category_id'], $category['items']);
				if ($categoryObject) {
					$categories[] = $categoryObject;
					$categoryMap[$categoryObject->category_id] = $categoryObject;
					$this->_estateTypeCategoryMap[$estateType['estate_type']]=$categoryMap;
				}
			}
			$estateTypeFiletr = [];
			$estateTypeFiletr['estate_type'] = $estateType['estate_type'];
			$estateTypeFiletr['categories'] = $categories;
			$this->estate_types[] =$estateTypeFiletr;
		}
	}


	protected function _loadEnables($estateClass, $targetItemIds = null) {

		$typeList = TypeList::getInstance()->getByClass($estateClass);
		foreach ($typeList as $estateType=>$lavel){

			$categories = [];
			$categoryItems = $this->_enabledItemConfig->getAllCategoryItemId();
			// while (list($categoryId,$itemIds) = each($categoryItems)) {
			foreach($categoryItems as $categoryId => $itemIds){
				// ロード対象を設定
				$multiItemIds = [];
				if ($targetItemIds) {
					if (isset($targetItemIds[$categoryId])) {
						$multiItemIds = $targetItemIds[$categoryId];
					}
				}
				else {
					$multiItemIds = $itemIds;
				}

				// ロード
				if (!($items = $this->_itemFactory->createMulti($estateType, $categoryId, $multiItemIds))) {
					// ロードアイテムがない場合
					if ($myCategory = $this->getCategory($estateType, $categoryId)) {
						$categories[] = $myCategory;
					}
					continue;
				}

				$newItems = [];
				$myCategory = $this->getCategory($estateType, $categoryId);
				if ($myCategory) {
					// アイテムマップ作成
					$itemsMap = [];
					foreach ($items as $item) {
						$itemsMap[$item->item_id] = $item;
					}

					// アイテムID順にアイテム取得
					foreach ($itemIds as $itemId) {
						if ($myItem = $myCategory->getItem($itemId)) {
							$newItems[] = $myItem;
						}
						else if (isset($itemsMap[$itemId])) {
							$itemsMap[$itemId]->setIsLoaded();
							$newItems[] = $itemsMap[$itemId];
						}
					}
				}
				else {
					foreach ($items as $item) {
						$item->setIsLoaded();
					}
					$newItems = $items;
				}
				$category = $this->_categoryFactory->create($estateType, $categoryId);
				$category->items = $newItems;
				$category->updateItemMap();
				$categories[] = $category;
			}
			$estateTypeFiletr['estate_type'] = $estateType;
			$estateTypeFiletr['categories'] = $categories;
			$estateTypes[] = $estateTypeFiletr;
		}

		$this->estate_types = $estateTypes;
//		$this->categories = $categories;
		$this->updateCategoryMap();
		return $this;
	}

	//public function getCategory($estateType, $categoryId) {
	public function getCategory($categoryId) {
//		if (!isset($this->_estateTypeCategoryMap[$estateType])){
//			return null;
//		}
//		if (!isset($this->_estateTypeCategoryMap[$estateType])){
//			return null;
//		}

		return isset($this->_categoryMap[$categoryId]) ? $this->_categoryMap[$categoryId]: null;
	}

	/**
	 * ユーザサイトからの検索条件を受け取る
	 * @param int $estateType
	 * @param array $values
	 */
	public function setValues($estateType, $values) {

		// 希望条件の初期値をクリア
		foreach ($this->pickDesiredCategories() as $category) {
			$category->unsetValueToNotLoadedItems();
		}

		if (!is_array($values)) {
			return $this;
		}

		// アイテムのロード
		$targetItemIds = [];
		foreach ($values as $categoryId => $items) {
			if (!is_array($items)) {
				continue;
			}
			$targetItemIds[$categoryId] = array_keys($items);
		}
		if ($targetItemIds) {
			$this->_loadEnables($estateType, $targetItemIds);
		}

		// 値セット
		foreach ($values as $categoryId => $items) {
			$category = $this->getCategory($categoryId);
			if (!$category) {
				continue;
			}
			$category->setValues($items);
		}
		return $this;
	}

	public function isDesiredCategory($categoryId) {
		return in_array($categoryId, $this->_desiredCategories);
	}

	public function pickDesiredCategories() {
		$categories = [];
		foreach ($this->categories as $category) {
			if (!$this->isDesiredCategory($category->category_id)) {
				continue;
			}
			$categories[] = $category;
		}
		return $categories;
	}

	public function pickParticularCategories() {
		$categories = [];
		foreach ($this->categories as $category) {
			if ($this->isDesiredCategory($category->category_id)) {
				continue;
			}
			$categories[] = $category;
		}
		return $categories;
	}

	public function selectAll() {
		foreach ($this->categories as $category) {
			foreach ($category->items as $item) {
				switch ($item->getType()) {
					case 'multi':
						$item->item_value = ['10'];
						break;
					case 'list':
					case 'radio':
						$item->item_value = '10';
						break;
					default:
						$item->item_value = 1;
						break;
				}
			}
		}
	}
}