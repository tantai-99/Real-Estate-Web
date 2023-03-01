<?php
namespace Library\Custom\Estate\Setting\SearchFilter;
use Library\Custom\Model\Estate\TypeList;
use Modules\V1api\Models;
class SearchFilterAbstract {
	
    protected $_desiredCategories = [
        'shumoku',
        'kakaku',
        'rimawari',
        'torihiki_taiyo',
        'keiyaku_joken',
        'madori',
        'menseki',
        'tatemono_kozo',
        'saiteki_yoto',
        'eki_toho_fun',
        'chikunensu',
        'reform_renovation',
        'reformable_parts',
        'open_room',
        'open_house',
        'genchi_hanbaikai',
        'joho_kokai',
        'pro_comment',
        'image',
    ];
    
    public $categories;
	protected $_categoryMap;
	
	/**
	 * @var Library\Custom\Estate\Setting\SearchFilter\Config\Abstract\EnabledItem
	 */
	protected $_enabledItemConfig;
	
	/**
	 * 
	 * @var Library\Custom\Estate\Setting\SearchFilter\Item\Factory
	 */
	protected $_itemFactory;
	
	/**
	 * 
	 * @var Library\Custom\Estate\Setting\SearchFilter\Category\Factory
	 */
	protected $_categoryFactory;
	
	protected $_parsed = false;

    protected $_isValueEmpty = false;
	
	public function __construct() {
		$this->init();
	}
	
	public function init() {
		$this->categories = [];
		$this->_categoryMap = [];
	}
	
	public function updateCategoryMap() {
		$this->_categoryMap = [];
		foreach ($this->categories as $category) {
			$this->_categoryMap[ $category->category_id ] = $category;
		}
	}
	
	/**
	 * マスタとして利用できるようにする
	 */
	public function asMaster() {
		foreach ($this->categories as $category) {
			$category->asMaster();
		}
	}
	
	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($estateType, $values) {
        // 特集の複数種目に対応
        $estateType = TypeList::getInstance()->getCompositeType($estateType);

		$this->_parsed = true;
		
		if (!is_array($values)) {
			$values = @json_decode($values, true);
		}
		if (!isset($values['categories']) || !is_array($values['categories'])) {
			return;
		}

		foreach ($values['categories'] as $category) {
			if (!is_array($category)) {
				continue;
			}
			if (!isset($category['category_id'])) {
				continue;
			}
			if (empty($category['items'])) {
				continue;
			}
			
			$categoryObject = $this->_categoryFactory->create($estateType, $category['category_id'], $category['items']);
			if ($categoryObject) {
				$this->categories[] = $categoryObject;
				$this->_categoryMap[$categoryObject->category_id] = $categoryObject;
			}
		}
	}

    public function isValueEmpty() {
        return $this->_isValueEmpty;
    }


	public function isParsed() {
		return $this->_parsed;
	}

	public function setParsed($bool) {
		$this->_parsed = $bool;
	}

	/**
	 * 物件種目(種別)内で有効なアイテムをロードする
	 * @param int $estateType
	 */
	public function loadEnables($estateType) {
	    return $this->_loadEnables($estateType);
	}
	
	/**
	 * 物件種目(種別)内で有効な希望条件アイテムをロードする
	 * @param int $estateType
	 */
	public function loadDesiredEnables($estateType) {
	    return $this->_loadEnables($estateType, $this->_enabledItemConfig->pickCategoryItemId( $this->_desiredCategories ));
	}
	
	/**
	 * 人気のこだわり条件ロード
	 */
	public function loadPopularItems($estateType) {
		$model = Models\PopularItemList::getInstance();
		return $this->_loadEnables($estateType, $model->getItemIdsByCategory($estateType));
	}
	
	protected function _loadEnables($estateType, $targetItemIds = null) {
        // 特集の複数種目に対応
        $estateType = TypeList::getInstance()->getCompositeType($estateType);

		$categories = [];
		$categoryItems = $this->_enabledItemConfig->getAllCategoryItemId();
		foreach ($categoryItems as $categoryId => $itemIds) {
		// while (list($categoryId,$itemIds) = each($categoryItems)) {
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
			    if ($myCategory = $this->getCategory($categoryId)) {
			        $categories[] = $myCategory;
			    }
				continue;
			}
			
			$newItems = [];
			$myCategory = $this->getCategory($categoryId);
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
		$this->categories = $categories;
		$this->updateCategoryMap();
		return $this;
	}
	
	public function getCategory($categoryId) {
		return isset($this->_categoryMap[$categoryId]) ? $this->_categoryMap[$categoryId]: null;
	}
	
	/**
	 * ユーザサイトからの検索条件を受け取る
	 * @param int $estateType
	 * @param array $values
	 */
	public function setValues($estateType, $values) {
        if (empty($values) || (count($values) == 1 && array_key_exists('fulltext_fields', $values))) {
            $this->_isValueEmpty = true;
        }

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
		
	public function setSearchFilterDefault($params) {
		if (($params->isPcMedia() && empty($params->getSearchFilter())) 
		|| ($params->isSpMedia() && !$params->getSpSession())) {
			foreach($this->categories as $category) {
				if($category->category_id == 'shumoku') {
					foreach($category->items as $item) {
	                    if ($item->item_id == '39') {
							$item->item_value = 1;
							break 2;
						}
	                }
					
				}
			}
		}
		return $this;
	}

	public function checkSearchFiltershumoku($valShumoku) {
		foreach($this->categories as $category) {
			if($category->category_id == 'shumoku') {
				foreach($category->items as $item) {
					if ($item->item_id == $valShumoku && $item->item_value == 1) {
						return true;
					}
				}
			}
		}
	return false;
	}
}