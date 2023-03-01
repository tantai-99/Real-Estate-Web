<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Library\Custom\Estate\Setting\Basic;
use Library\Custom\Model\Estate\TypeList;
use App\Collections\EstateClassSearchCollection;

class EstateClassSearch extends Model
{
	use MySoftDeletes;

	protected $table = 'estate_class_search';
	public $timestamps = false;
	const DELETED_AT = 'delete_flg';
	const ESTATE_LINK_TYPE = 'type';

	protected $fillable = [
		'id',
		'origin_id',
		'estate_search_id',
		'hp_estate_setting_id',
		'hp_id',
		'estate_class',
		'enabled_estate_type',
		'map_search_here_enabled',
		'area_search_filter',
		'estate_request_flg',
		'display_freeword',
		'display_fdp',
		'delete_flg',
		'create_id',
		'create_date',
		'update_id',
		'update_date',
	];

	protected $_settingObject;

	public function toSettingObject($cache = true)
	{
		if (!$cache) {
			return new Basic($this->toArray());
		}

		if (!$this->_settingObject) {
			$this->_settingObject = new Basic($this->toArray());
		}
		return $this->_settingObject;
	}

	/**
	 * !公開情報を持っている事
	 */
	public function toSiteMapArray()
	{
		$result = [];

		$public = [];
		if ($this->is_public) {
			foreach (explode(',', $this->public_estate_type) as $type) {
				$public[$type] = true;
			}
		}
		foreach ($this->getEnabledEstateTypeArray() as $i => $type) {
			$data = [];
			$data['estate_page_type']	= 'estate_' . static::ESTATE_LINK_TYPE;
			$data['estate_class']		= $this->estate_class;
			$data['estate_type']		= $type;
			$data['id']					= $type;
			$data['link_id']			= $this->getLinkId($type);
			$data['title']				= $this->getTitle($type);
			$data['filename']			= $this->getFilename($type);
			$data['public_flg']			= isset($public[$type]);
			$data['deleted']			= false;
			$data['update_date']	    = $this->update_date;

			$result[] = $data;
		}
		return $result;
	}

	public function getEnabledEstateTypeArray()
	{
		return explode(',', $this->enabled_estate_type);
	}

	public function isEnabledEstateType($type)
	{
		return in_array((string)$type, $this->getEnabledEstateTypeArray(), true);
	}

	public function getLinkIdList($withFilename = false)
	{
		$result = [];
		foreach ($this->getEnabledEstateTypeArray() as $i => $type) {
			$result[$this->getLinkId($type)] = $this->getTitle($type, $withFilename);
		}
		return $result;
	}

	public function getLinkId($type)
	{
		return 'estate_' . static::ESTATE_LINK_TYPE . '_' . $type;
	}

	public function getTitle($type, $withFilename = false)
	{
		$title = TypeList::getInstance()->get($type);
		if ($withFilename) {
			$title .= '（' . $this->getFilename($type) . '）';
		}
		return $title;
	}

	public function getFilename($type)
	{
		return TypeList::getInstance()->getUrl($type);
	}

	public function hasAreaSearchType()
	{
		return $this->toSettingObject()->area_search_filter->hasAreaSearchType();
	}

	public function hasLineSearchType()
	{
		return $this->toSettingObject()->area_search_filter->hasLineSearchType();
	}

	public function hasSpatialSearchType()
	{
		return $this->toSettingObject()->area_search_filter->hasSpatialSearchType();
	}

	public function hasMapSearchHere()
	{
		return $this->toSettingObject()->map_search_here_enabled;
	}

	public function newCollection(array $models = array())
	{
		return new EstateClassSearchCollection($models);
	}

    public function getPrefs() {
        $result = [];
        if (is_array($this)) {
            foreach ($this as $row) {
                $setting = $row->toSettingObject();
                $result = array_merge($result, $setting->area_search_filter->area_1);
            }
        } else {
            $setting = $this->toSettingObject();
            $result = array_merge($result, $setting->area_search_filter->area_1);
        }
        return $result;
    }

    public function getEstateTypes() {
        $result = [];
        if (is_array($this)) {
            foreach ($this as $row) {
                $result = array_merge($result, $row->getEnabledEstateTypeArray());
            }
        } else {
            $result = array_merge($result, $this->getEnabledEstateTypeArray());
        }
        return $result;
    }
}

