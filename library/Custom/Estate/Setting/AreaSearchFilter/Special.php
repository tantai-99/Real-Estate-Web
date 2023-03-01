<?php
namespace Library\Custom\Estate\Setting\AreaSearchFilter;

use Library\Custom\Model\Estate;

class Special extends Basic {

	/**
	 * @var int
	 */
	public $has_search_page;
    public $search_type_condition;

	public function init() {
		parent::init();
        $this->has_search_page = null;
        $this->search_condition = ['type' => 0, 'count'=> 0];
	}

	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($values) {
		if (!is_array($values)) {
            $values = @json_decode($values, true);
		}

		parent::parse($values);

		if (isset($values['has_search_page'])) {
			$this->has_search_page = (int)$values['has_search_page'];
        }
        if (isset($values['search_condition'])) {
            $this->search_condition['type'] = (int)$values['search_condition']['type'];
			$this->search_condition['count'] = (int)$values['search_condition']['count'];
        }
	}

	public function getDisplayHasSearchPage() {
		return Estate\SpecialSearchPageTypeList::getInstance()->get($this->has_search_page);
	}

	public function hasAreaSearchType() {
	    return in_array(Estate\SearchTypeList::TYPE_AREA, $this->search_type);
	}

	public function hasLineSearchType() {
	    return in_array(Estate\SearchTypeList::TYPE_ENSEN, $this->search_type);
	}

	public function hasSpatialSearchType() {
		return in_array(Estate\SearchTypeList::TYPE_SPATIAL, $this->search_type);
	}
    
    public function hasAreaSearchTypeCondition() {
        return $this->search_condition['type'] == Estate\SearchTypeCondition::TYPE_CITY 
        || $this->search_condition['type'] == Estate\SearchTypeCondition::TYPE_CHOSON;
    }
    
    public function hasLineSearchTypeCondition() {
	    return $this->search_condition['type'] == Estate\SearchTypeCondition::TYPE_ENSEN;
	}
    
    public function canChosonSearchCondition() {
        return $this->search_condition['type'] == Estate\SearchTypeCondition::TYPE_CHOSON;
    }

    public function getShozaichiCodesCondition($filterPrefCodes = [], $filterShikugunCodes = []) {
	    if (!$filterPrefCodes) {
	        $filterPrefCodes = $this->area_1;
        } elseif (!is_array($filterPrefCodes)) {
	        $filterPrefCodes = [$filterPrefCodes];
        }
        if (!$filterShikugunCodes) {
	        $filterShikugunCodes = [];
        } elseif (!is_array($filterShikugunCodes)) {
	        $filterShikugunCodes = [$filterShikugunCodes];
        }

        $shozaichiCodes = [];
	    foreach ($filterPrefCodes as $prefCode) {
	        if (!in_array($prefCode, $this->area_1)) {
                // 設定されていない都道府県はSkip
	            continue;
            }

	        foreach ($this->area_2->getDataByPref($prefCode) as $shikugunCode) {
	            if ($filterShikugunCodes && !in_array($shikugunCode, $filterShikugunCodes)) {
                    // 対象外の市区群はSkip
                    continue;
                }

                if (
                    // 町村設定がない
                    (
                        !isset($this->area_5[$prefCode][$shikugunCode]) ||
                        !is_array($this->area_5[$prefCode][$shikugunCode]) ||
                        !$this->area_5[$prefCode][$shikugunCode]
                    )
                ) {
                    $shozaichiCodes[] = $shikugunCode;
	                continue;
                }

                // 町村コード付与
                foreach ($this->area_5[$prefCode][$shikugunCode] as $chosonCode) {
                    // 都道府県 -> 市区郡 -> 町村 でたどり、町村詳細(area_6)の有無をチェックする
                    if( isset($this->area_6)
                     && isset($this->area_6[$prefCode])
                     && isset($this->area_6[$prefCode][$shikugunCode])
                     && isset($this->area_6[$prefCode][$shikugunCode][$chosonCode]) ) {
                        foreach ($this->area_6[$prefCode][$shikugunCode][$chosonCode] as $choazaCode) {
                            $shozaichiCodes[] = "{$shikugunCode}:{$choazaCode}";
                        }
                    } else {
	                    $shozaichiCodes[] = "{$shikugunCode}:{$chosonCode}";
                    }
                }
            }
        }

        return $shozaichiCodes;
    }
}