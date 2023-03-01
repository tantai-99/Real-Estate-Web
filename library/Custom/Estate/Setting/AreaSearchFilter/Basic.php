<?php
namespace Library\Custom\Estate\Setting\AreaSearchFilter;

use Library\Custom\Model\Estate;
use ArrayObject;
use Library\Custom\Model\Estate\SecondSearchTypeList;

class Basic {

	/**
	 * @var array
	 */
	public $search_type;

    /**
     * @var integer
     */
	public $choson_search_enabled;

	/**
     * 都道府県
	 * @var array
	 */
	public $area_1;
	/**
     * 市区軍
	 * @var Library\Custom\Estate\Setting\AreaSearchFilter\AreaData
	 */
	public $area_2;
	/**
     * 沿線
	 * @var Library\Custom\Estate\Setting\AreaSearchFilter\AreaData
	 */
	public $area_3;
	/**
     * 駅
	 * @var Library\Custom\Estate\Setting\AreaSearchFilter\AreaData
	 */
	public $area_4;
    /**
     * 町村
     * @var Library\Custom\Estate\Setting\AreaSearchFilter\AreaData
     */
    public $area_5;
    /**
     * 町字
     * @var Library\Custom\Estate\Setting\AreaSearchFilter\AreaData
     */
    public $area_6;

	public function __construct($values = null) {
        $this->init();
		if ($values) {
			$this->parse($values);
		}
	}

	public function init() {
		$this->search_type = [];
		$this->choson_search_enabled = 0;
		$this->area_1 = [];
		$this->area_2 = new AreaData([], ArrayObject::ARRAY_AS_PROPS);
		$this->area_3 = new AreaData([], ArrayObject::ARRAY_AS_PROPS);
        $this->area_4 = new AreaData([], ArrayObject::ARRAY_AS_PROPS);
        $this->area_5 = new AreaData([], ArrayObject::ARRAY_AS_PROPS);
        $this->area_6 = new AreaData([], ArrayObject::ARRAY_AS_PROPS);
	}

	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($values) {
		if (!is_array($values)) {
			$values = @json_decode($values, true);
        }
        
		$arrays = ['area_1'];
		$strings = [];
		$integers = ['choson_search_enabled'];

		if (is_array($this->search_type)) {
			$arrays[] = 'search_type';
		}
		else {
			$strings[] = 'search_type';
		}
		foreach ($arrays as $prop) {
			if (isset($values[$prop]) && is_array($values[$prop])) {
				$this->{$prop} = $values[$prop];
			}
		}

		foreach ($strings as $prop) {
			if (isset($values[$prop]) && !is_array($values[$prop])) {
				$this->{$prop} = (string) $values[$prop];
			}
		}

        foreach ($integers as $prop) {
            if (isset($values[$prop]) && is_numeric($values[$prop])) {
                $this->{$prop} = (int) $values[$prop];
            }
        }

        foreach (['area_2', 'area_3', 'area_4', 'area_5', 'area_6'] as $prop) {
			if (!isset($values[$prop]) || !is_array($values[$prop])) {
				continue;
            }
			foreach ($values[$prop] as $pref => $codes) {
				if (is_array($codes)) {
                    $this->{$prop}->{$pref} = $codes;
				}
            }
        }
	}

	public function getDisplaySearchType() {
		return Estate\SearchTypeList::getInstance()->pick($this->search_type);
	}

	public function getDisplayPref() {
		return Estate\PrefCodeList::getInstance()->pick($this->area_1);
	}

	public function hasAreaSearchType() {
	    return in_array(Estate\SearchTypeList::TYPE_AREA, $this->search_type);
	}

    public function canChosonSearch() {
        return $this->choson_search_enabled == 1;
    }

	public function hasLineSearchType() {
	    return in_array(Estate\SearchTypeList::TYPE_ENSEN, $this->search_type);
	}

	public function hasSpatialSearchType() {
		return in_array(Estate\SearchTypeList::TYPE_SPATIAL, $this->search_type);
	}

	public function getShikugunCodes($prefCode) {
	    return $this->area_2->getDataByPref($prefCode);
    }

    public function hasAreaLineSearchType() {
        return ($this->hasAreaSearchType() || $this->hasSpatialSearchType()) && $this->hasLineSearchType();
    }

    /**
     * 町村コードまで含めた所在地コードを取得する
     * @param int|array $filterPrefCodes 省略時は設定されているすべての都道府県
     * @param int|array $filterShikugunCodes 省略時は設定されているすべての市区群
     */
	public function getShozaichiCodes($filterPrefCodes = [], $filterShikugunCodes = []) {
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
                    // 町村検索設定でない
                    (!$this->canChosonSearch()) ||
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

    /**
     * 町村検索時、その町村コードもしくはその詳細まで含めた所在地コードを設定する
     * @param ref:Array &$shozaichiCodes 所在地コードリスト
     * @param int       $prefCode     都道府県コード
     * @param int       $shikugunCode 市区郡コード
     * @param int       $chosonCode   町村コード
     */
    public function getShozaichiCodesByChoson(&$shozaichiCodes, $prefCode, $shikugunCode, $chosonCode) {
        // 都道府県 -> 市区郡 -> 町村 でたどり、町村詳細(area_6)の有無をチェックする
        if( isset($this->area_6)
         && isset($this->area_6[$prefCode])
         && isset($this->area_6[$prefCode][$shikugunCode])
         && isset($this->area_6[$prefCode][$shikugunCode][$chosonCode]) ) {
            // 町村詳細あり 所在地コード: <市区郡コード>:<町村詳細コード>
            foreach($this->area_6[$prefCode][$shikugunCode][$chosonCode] as $choazaCode) {
                $shozaichiCodes[] = "{$shikugunCode}:{$choazaCode}";
            }
        } else {
            // 町村詳細なし 所在地コード: <市区郡コード>:<町村コード>
            $shozaichiCodes[] = "{$shikugunCode}:{$chosonCode}";
        }
    }
}
