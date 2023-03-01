<?php
namespace Library\Custom\Estate\Setting;

use Library\Custom\Estate\Setting\AreaSearchFilter;
use Library\Custom\Estate\Setting\SearchFilter;
use Library\Custom\Estate\Setting\SearchFilterForBapi;
use Library\Custom\Model\Estate\SecondEstateEnabledList;

class Second extends Basic {
	
	/**
	 * @var int
	 */
	public $enabled;

	public $search_filter_for_bapi;

	/**
	 * @param  Library\Custom\Estate\Setting\Second
	 */
	public $area_search_filter;
	/**
	 * @param  Library\Custom\Estate\Setting\Second
	 */
	public $search_filter;
	
	public function __construct($values = null) {
		$this->init();
		if ($values) {
			$this->parse($values);
		}
	}
	
	public function init() {
		parent::init();
		$this->enabled = null;
		$this->area_search_filter = new AreaSearchFilter\Second();
		$this->search_filter = new SearchFilter\Second();
		$this->search_filter_for_bapi = new SearchFilterForBapi\Second();
	}
	
	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($values) {
		if (!is_array($values)) {
			return;
		}
		
		parent::parse($values);
		
		if (isset($values['enabled'])) {
			$this->enabled = (int)$values['enabled'];
		}
		if (isset($values['search_filter'])) {
			$this->search_filter->parse($this->estate_class, $values['search_filter']);
			
			if(isset($values['enabled_estate_type'])){
				$this->search_filter_for_bapi->parse($this->estate_class, $values['enabled_estate_type'], $values['search_filter']);
			}
		}
	}
	
	public function getDisplayEnabled() {
		return SecondEstateEnabledList::getInstance()->get($this->enabled);
	}
	public function notIncludes(){
		return [
			'display_fdp',
			'map_search_here_enabled',
			'estate_request_flg',
			'display_freeword',
		];
	}
}