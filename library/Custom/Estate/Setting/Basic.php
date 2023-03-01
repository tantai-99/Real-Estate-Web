<?php
namespace Library\Custom\Estate\Setting;
use Library\Custom\Model\Estate\TypeList;

class Basic extends SettingAbstract {

	/**
	 * @var string
	 */
	public $estate_class;
	/**
	 * @var array
	 */
	public $enabled_estate_type;
	/**
	 * @var boolean
	 */
	public $map_search_here_enabled;
	/**
	 * @var Library\Custom\Estate\Setting\AreaSearchFilter\Basic
	 */
	public $area_search_filter;

	/**
	 * 物件リクエスト
	 * @var boolean
	 */
	public $estate_request_flg;

	/**
	 * @var boolean
	 */
	public $display_freeword;
    /**
     * @var string
     */
    public $display_fdp;

    public $enabled;

	public function __construct($values = null) {
		$this->init();
		if ($values) {
			$this->parse($values);
		}
	}

	public function init() {
		$this->estate_class = null;
		$this->enabled = [];
		$this->enabled_estate_type = [];
		$this->area_search_filter = new AreaSearchFilter\Basic();
		//物件リクエスト
		$this->estate_class = null;
		$this->display_fdp = array('fdp_type' => [], 'town_type' => []);
	}

	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($values) {
		if (!is_array($values)) {
			return;
		}

		if (isset($values['estate_class'])) {
			$this->estate_class = $values['estate_class'];
		}
		if (isset($values['enabled_estate_type'])) {
			if (is_array($values['enabled_estate_type'])) {
				$this->enabled_estate_type = $values['enabled_estate_type'];
			}
			else {
				$this->enabled_estate_type = explode(',', $values['enabled_estate_type']);
			}
		}
		if (isset($values['area_search_filter'])) {
			$this->area_search_filter->parse($values['area_search_filter']);
		}
		if (isset($values['map_search_here_enabled'])) {
			$this->map_search_here_enabled = $values['map_search_here_enabled'];
		}
		//物件リクエスト
		if (isset($values['estate_request_flg'])) {
			$this->estate_request_flg = $values['estate_request_flg'];
		}
		if (isset($values['display_freeword'])) {
			$this->display_freeword = $values['display_freeword'];
		}
		if (isset($values['display_fdp'])) {
			$this->display_fdp = $values['display_fdp'];
		}
	}

	public function getDisplayEstateType() {
		return TypeList::getInstance()->pick($this->enabled_estate_type);
	}
}