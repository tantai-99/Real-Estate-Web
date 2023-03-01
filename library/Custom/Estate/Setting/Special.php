<?php
namespace Library\Custom\Estate\Setting;
use Library\Custom\Model\Estate\TypeList;
use Library\Custom\Model\Estate\ShumokuSort;
use Library\Custom\Estate\Setting\SearchFilter;
use Library\Custom\Estate\Setting\AreaSearchFilter;

class Special extends SettingAbstract {

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $origin_id;

	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var string
	 */
	public $description;
	/**
	 * @var array
	 */
	public $keywords;
	/**
	 * @var string
	 */
	public $filename;
	/**
	 * @var string
	 */
	public $comment;
	/**
	 * @var string
	 */
	public $estate_class;
	/**
	 * @var array
	 */
	public $enabled_estate_type;
	/**
	 * @var int
	 */
	public $only_er_enabled;
	/**
	 * @var int
	 */
	public $second_estate_enabled;
	/**
	 * @var int
	 */
	public $end_muke_enabled;
	/**
	 * @var int
	 */
	public $only_second;
	/**
	 * @var int
	 */
	public $exclude_second;
	/**
	 * @var Library\Custom\Estate\Setting\AreaSearchFilter\Special
	 */
	public $area_search_filter;

	/**
	 * @var Library\Custom\Estate\Setting\SearchFilter\Special
	 */
	public $search_filter;

	/**
	 * @var int
	 */
	public $display_freeword;

	/**
	 * ATHOME_HP_DEV-3982 追加
	 */
	public $owner_change;

	public $jisha_bukken;
	public $niji_kokoku;
	public $niji_kokoku_jido_kokai;

	public $tesuryo_ari_nomi;
	public $tesuryo_wakare_komi;
	public $kokokuhi_joken_ari;
    public $method_setting;

	public function __construct($values = null) {
        $this->init();
		if ($values) {
			$this->parse($values);
		}
	}

	public function init() {
		$this->id = null;
		$this->origin_id = null;
		$this->title = null;
		$this->description = null;
		$this->keywords = [];
		$this->filename = null;
		$this->comment = null;
		$this->estate_class = null;
		$this->enabled_estate_type = [];
		$this->map_search_here_enabled = 0;
		$this->only_er_enabled = null;
		$this->second_estate_enabled = null;
		$this->end_muke_enabled = 0;
		$this->only_second = 0;
        $this->exclude_second = 0;
        $this->houses_id = [];
		$this->area_search_filter = new AreaSearchFilter\Special();
		$this->search_filter = new SearchFilter\Special();
        $this->method_setting = 1;
	}

	/**
	 * 型チェック
	 * @param array $values
	 */
	public function parse($values) {
		if (!is_array($values)) {
			return;
		}

		if (isset($values['id']) && !is_null($values['id'])) {
			$this->id = $values['id'];
		}
		if (isset($values['origin_id']) && !is_null($values['origin_id'])) {
			$this->origin_id = $values['origin_id'];
		}
		if (isset($values['title']) && !is_null($values['title'])) {
			$this->title = $values['title'];
		}
		if (isset($values['description']) && !is_null($values['description'])) {
			$this->description = $values['description'];
		}
		if (isset($values['keywords']) && !is_null($values['keywords'])) {
			$this->keywords = is_array($values['keywords']) ? $values['keywords'] : explode(',', $values['keywords']);
		}
		if (isset($values['filename']) && !is_null($values['filename'])) {
			$this->filename = $values['filename'];
		}
		if (isset($values['comment']) && !is_null($values['comment'])) {
			$this->comment = $values['comment'];
		}

		if (isset($values['estate_class']) && !is_null($values['estate_class'])) {
			$this->estate_class = $values['estate_class'];
		}
		if (isset($values['enabled_estate_type']) && !is_null($values['enabled_estate_type'])) {
            if (is_array($values['enabled_estate_type'])) {
                $this->enabled_estate_type = $values['enabled_estate_type'];
            }
            else {
                $this->enabled_estate_type = explode(',', $values['enabled_estate_type']);
            }
			if (!$this->estate_class) {
				$this->estate_class =
					TypeList::getInstance()->getClassByType($this->enabled_estate_type[0]);
			}
		}
		if (isset($values['only_er_enabled']) && !is_null($values['only_er_enabled'])) {
			$this->only_er_enabled = (int)$values['only_er_enabled'];
		}
		if (isset($values['second_estate_enabled']) && !is_null($values['second_estate_enabled'])) {
			$this->second_estate_enabled = (int)$values['second_estate_enabled'];
		}
		if (isset($values['end_muke_enabled']) && !is_null($values['end_muke_enabled'])) {
			$this->end_muke_enabled = (int)$values['end_muke_enabled'];
		}
		if (isset($values['only_second']) && !is_null($values['only_second'])) {
			$this->only_second = (int)$values['only_second'];
		}
		if (isset($values['exclude_second']) && !is_null($values['exclude_second'])) {
			$this->exclude_second = (int)$values['exclude_second'];
        }

        if (isset($values['houses_id']) && !is_null($values['houses_id'])) {
            if (is_array($values['houses_id'])) {
                if (empty($values['houses_id'])) {
                    $this->houses_id = null;
                } else {
                    $this->houses_id = $values['houses_id'];
                }
            }
            else {
                if ($values['houses_id']) {
                    $this->houses_id = explode(',', $values['houses_id']);
                }
            }
		}

		if (isset($values['area_search_filter']) && !is_null($values['area_search_filter'])) {
			$this->area_search_filter->parse($values['area_search_filter']);
		}
		if (isset($values['search_filter']) && !is_null($values['search_filter'])) {
			$this->search_filter->parse($this->enabled_estate_type, $values['search_filter']);
		}
		if (isset($values['map_search_here_enabled']) && !is_null($values['map_search_here_enabled'])) {
			$this->map_search_here_enabled = $values['map_search_here_enabled'];
		}
		if (isset($values['display_freeword']) && !is_null($values['display_freeword'])) {
			$this->display_freeword = $values['display_freeword'];
		}
		if (isset($values['owner_change']) && !is_null($values['owner_change'])) {
			$this->owner_change = $values['owner_change'];
		}
		if (isset($values['jisha_bukken']) && !is_null($values['jisha_bukken'])) {
			$this->jisha_bukken = $values['jisha_bukken'];
		}
		if (isset($values['niji_kokoku']) && !is_null($values['niji_kokoku'])) {
			$this->niji_kokoku = $values['niji_kokoku'];
		}
		if (isset($values['niji_kokoku_jido_kokai']) && !is_null($values['niji_kokoku_jido_kokai'])) {
			$this->niji_kokoku_jido_kokai = $values['niji_kokoku_jido_kokai'];
		}
		if (isset($values['tesuryo_ari_nomi']) && !is_null($values['tesuryo_ari_nomi'])) {
			$this->tesuryo_ari_nomi = $values['tesuryo_ari_nomi'];
		}
		if (isset($values['tesuryo_wakare_komi']) && !is_null($values['tesuryo_wakare_komi'])) {
			$this->tesuryo_wakare_komi = $values['tesuryo_wakare_komi'];
		}
		if (isset($values['kokokuhi_joken_ari']) && !is_null($values['kokokuhi_joken_ari'])) {
			$this->kokokuhi_joken_ari = $values['kokokuhi_joken_ari'];
		}
        if (isset($values['method_setting']) && !is_null($values['method_setting'])) {
            $this->method_setting = $values['method_setting'];
        }
	}

    public function getDisplayEstateType() {
        return TypeList::getInstance()->pick($this->enabled_estate_type);
    }

	public function notIncludes() {
		return [
			'description',
			'keywords'
		];
	}

	public function isSpecialShumokuSort() {
		foreach(ShumokuSort::getInstance()->getAll() as $type => $values) {
			if ($type == implode($this->enabled_estate_type)) {
				return true;
			}
		}
        return false;
    }
}