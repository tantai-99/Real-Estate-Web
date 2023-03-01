<?php

namespace App\Collections;

use Library\Custom\Model\Estate\TypeList;

class EstateClassSearchCollection extends CustomCollection
{
	public function getEstateClasses()
	{
		$result = [];
		foreach ($this as $row) {
			$result[] = $row->estate_class;
		}
		return $result;
	}

	public function getEstateTypes()
	{
		$result = [];
		foreach ($this as $row) {
			$result = array_merge($result, $row->getEnabledEstateTypeArray());
		}
		return $result;
	}

	public function getPrefs()
	{
		$result = [];
		foreach ($this as $row) {
			$setting = $row->toSettingObject();
			$result = array_merge($result, $setting->area_search_filter->area_1);
		}
		return $result;
	}

	// 設定がない場合はNULL
	public function getPref($type)
	{
		foreach ($this as $row) {
			if (in_array((string)$type, $row->getEnabledEstateTypeArray())) {
				$setting = $row->toSettingObject();
				return (array) $setting->area_search_filter->area_1;
			}
		}
		return null;
	}

	// 設定がない場合はNULL
	public function getShikugun($type, $prefCode)
	{
		foreach ($this as $row) {
			if (in_array((string)$type, $row->getEnabledEstateTypeArray())) {
				$setting = $row->toSettingObject();
				return (array) $setting->area_search_filter->area_2->getDataByPref($prefCode);
			}
		}
		return null;
	}

	// 設定がない場合はNULL
	public function getEnsen($type, $prefCode)
	{
		foreach ($this as $row) {
			if (in_array((string)$type, $row->getEnabledEstateTypeArray())) {
				$setting = $row->toSettingObject();
				return (array) $setting->area_search_filter->area_3->getDataByPref($prefCode);
			}
		}
		return null;
	}

	// 設定がない場合はNULL
	// DBの駅設定をそのまま返す
	public function getKenEki($type)
	{
		$kenEkiList = null;
		foreach ($this as $row) {
			if (in_array((string)$type, $row->getEnabledEstateTypeArray())) {
				$setting = $row->toSettingObject();
				$kenEkiList = (array) $setting->area_search_filter->area_4;
				break;
			}
		}
		return $kenEkiList;
	}

	// 設定がない場合はNULL
	public function getEki($type, $prefCode, $ensenCdList)
	{
		$ekiList = null;
		foreach ($this as $row) {
			if (in_array((string)$type, $row->getEnabledEstateTypeArray())) {
				$setting = $row->toSettingObject();
				$ekiList = (array) $setting->area_search_filter->area_4->getDataByPref($prefCode);
				break;
			}
		}
		if (is_null($ekiList)) return null;

		$result = array();
		foreach ($ekiList as $eki) {
			$ensenCd = substr($eki, 0, 4);
			if (in_array((string)$ensenCd, $ensenCdList)) {
				array_push($result, $eki);
			}
		}
		if (count($result) == 0) $result = null;
		return $result;
	}

	public function hasAreaSearchType()
	{
		foreach ($this as $row) {
			if ($row->hasAreaSearchType()) {
				return true;
			}
		}
		return false;
	}

	public function hasLineSearchType()
	{
		foreach ($this as $row) {
			if ($row->hasLineSearchType()) {
				return true;
			}
		}
		return false;
	}

	public function hasSpatialSearchType()
	{
		foreach ($this as $row) {
			if ($row->hasSpatialSearchType()) {
				return true;
			}
		}
		return false;
	}

	public function getRowByUrl($typeCt)
	{
		$typeId = TypeList::getInstance()->getTypeByUrl($typeCt);
		foreach ($this as $row) {
			if (in_array((string)$typeId, $row->getEnabledEstateTypeArray())) {
				return $row;
			}
		}
		return null;
	}

	public function getRowByTypeId($typeId)
	{
		foreach ($this as $row) {
			if (in_array((string)$typeId, $row->getEnabledEstateTypeArray())) {
				return $row;
			}
		}
		return null;
	}

}
