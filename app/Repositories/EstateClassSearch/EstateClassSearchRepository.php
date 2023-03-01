<?php

namespace App\Repositories\EstateClassSearch;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use Library\Custom\Model\Estate\TypeList;

use function Symfony\Component\Translation\t;

class EstateClassSearchRepository extends BaseRepository implements EstateClassSearchRepositoryInterface
{	
	protected $_name = 'estate_class_search';
    public function getModel()
    {
        return \App\Models\EstateClassSearch::class;
    }
	public function getSettingAll($hpId, $settingId) {
		$params = [
			['hp_id', $hpId],
			['hp_estate_setting_id', $settingId],
		];
		return $this->fetchAll($params, ['ASC' => 'estate_class']);
	}

	/**
	 * フリーワード検索設定をしている物件種別毎の検索条件を全て取得する
	 * @param int $hpId
	 * @param int $settingId hp_estate_setting.id
	 */
	public function getSettingAllForFreeword($hpId, $settingId)
	{
		$params = [
			['hp_id', $hpId],
			['hp_estate_setting_id', $settingId],
			['display_freeword', 1]
		];
		return $this->fetchAll($params, ['ASC' => 'estate_class']);
	}

	/**
	 * 物件種別毎の検索条件を取得する
	 * @param int $hpId
	 * @param int $settingId hp_estate_setting.id
	 * @param int $class
	 * @return App\Models\EstateClassSearch
	 */
	public function getSetting($hpId, $settingId, $class) {
		$params = [
			['hp_id', $hpId],
			['hp_estate_setting_id', $settingId],
			['estate_class', $class]
		];
		return $this->fetchRow($params);
	}

	/**
	 * 物件”種目”を指定して物件種別毎の検索条件を取得する
	 * @param int $hpId
	 * @param int $settingId hp_estate_setting.id
	 * @param int $type
	 */
	public function getSettingByEstateType($hpId, $settingId, $type) {
		$class = TypeList::getInstance()->getClassByType($type);
		if (!$class) {
			return null;
		}
		$params = [
			['hp_id', $hpId],
			['hp_estate_setting_id', $settingId],
			['estate_class', $class]
		];
		$row = $this->fetchRow($params);
		if (!$row) {
			return null;
		}
		return $row->isEnabledEstateType($type) ? $row : null;
	}

	/**
	 * 物件種別を指定して検索条件を取得する(公開ステータス付き)
	 * @param int $hpId
	 * @param int $settingId hp_estate_setting.id
	 * @param int $estateClass
	 */
	public function getSettingWithPubStatusByEstateClass($hpId, $settingId, $estateClass) {
		$select = $this->_createSelectWithPubStatus(false, $hpId, $settingId, $estateClass);

		$row = $select->first();

		return $row;
	}

	/**
	 * 物件種別毎の検索条件を全て取得する(公開ステータス付き)
	 * @param int $hpId
	 * @param int $settingId hp_estate_setting.id
	 */
	public function getSettingAllWithPubStatus($hpId, $settingId) {
		$select = $this->_createSelectWithPubStatus(false, $hpId, $settingId);

		$rowset = $select->get();

		return $rowset;
	}

	/**
	 *
	 * @param boolean $calcFoundRows
	 * @param int $hpId
	 * @param int $settingId
	 * @param int $estateClass
	 * @param array $order
	 * @param int $count
	 * @param int $offset
	 * @return Select
	 */
	protected function _createSelectWithPubStatus($calcFoundRows, $hpId, $settingId, $estateClass = null, $order = null, $count = null, $offset = null) {
		$settingTable = App::make(HpEstateSettingRepositoryInterface::class);
		$pubSetting = $settingTable->getSettingForPublic($hpId);
		if ($pubSetting) {
			$pubSettingId = $pubSetting->id;
		}
		else {
			$pubSettingId = 0;
		}
		$select = $this->model->withoutGlobalScopes()->from($this->_name . ' AS cms');
		$cols = [];
		if ($calcFoundRows) {
			$select->selectRaw('SQL_CALC_FOUND_ROWS cms.*');
		}
		else {
			$select->selectRaw('cms.*');
		}
		$select->selectRaw('IFNULL(cms.update_date <= pub.update_date, 0) as pub_status,  pub.id IS NOT NULL as is_public, pub.enabled_estate_type as public_estate_type');
		$select->leftJoin($this->_name.' AS pub', function($join) use ($pubSettingId) {
			$this->_createCmsJoinPubCondition($pubSettingId, $join);
		});
		$select->where('cms.hp_id', $hpId);
		$select->where('cms.hp_estate_setting_id', $settingId);
		if ($estateClass) {
			$select->where('cms.estate_class', $estateClass);
		}
		$select->where('cms.delete_flg', 0);

		if ($order) {
			$select->orderBy($order);
		}

		if ($count !== null || $offset !== null) {
			$select->skip($offset)->take($count);
		}

		return $select;
	}

	protected function _createCmsJoinPubCondition($pubSettingId, &$join) {
		$join->on('cms.origin_id', 'pub.origin_id')
			->where('pub.hp_estate_setting_id', $pubSettingId)
			->on('cms.hp_id', 'pub.hp_id')
			->where('pub.delete_flg', 0);
	}

	/**
	 * 物件種別毎の検索条件を保存する
	 * @param int $hpId
	 * @param int $settingId hp_estate_setting.id
	 * @param Library\Custom\Estate\Setting\Basic $setting
	 * @return App\Models\EstateClassSearch
	 */
	public function saveSetting($hpId, $settingId, $setting) {
		$row = $this->getSetting($hpId, $settingId, $setting->estate_class);
		if (!$row) {
			$row = $this->create([
				'estate_search_id' => 0,
				'hp_estate_setting_id' => $settingId,
				'hp_id' => $hpId,
				'estate_class' => $setting->estate_class,
			]);
			$row->origin_id = $row->id;
		}

		if(!isset($setting->estate_request_flg)) {
			$setting->estate_request_flg = 0;
		} else {
			$setting->estate_request_flg = 1;
		}

		if(!isset($setting->display_freeword)) {
			$setting->display_freeword = 0;
		} else {
			$setting->display_freeword = 1;
		}

		$row->setFromArray([
			'enabled_estate_type' => implode(',', $setting->enabled_estate_type),
			'area_search_filter'  => json_encode($setting->area_search_filter),
			'map_search_here_enabled'  => $setting->map_search_here_enabled,
			//物件リクエスト
			'estate_request_flg'  => $setting->estate_request_flg,
			'display_freeword' => $setting->display_freeword,
			'display_fdp' => json_encode($setting->display_fdp),
		]);
		return $row->save();
	}

	/**
	 * 本番未反映のデータがあるか
	 * @param int $hpId
	 * @param int $cmsSettingId
	 * @param int $pubSettingId
	 */
	public function hasChanged($hpId, $cmsSettingId, $pubSettingId) {

		$select = $this->model->withoutGlobalScopes()->from($this->_name . ' AS cms');
        $select->join($this->_name.' AS pub', function($join) use ($pubSettingId) {
			$this->_createCmsJoinPubCondition($pubSettingId, $join);
        });
	    
		$select->where('cms.hp_id', $hpId);
		$select->where('cms.hp_estate_setting_id', $cmsSettingId);
		$select->where('cms.delete_flg', 0);
		$select->whereRaw('cms.update_date > pub.update_date');
	    
		$row = $select->first();

		return !!$row;
	}

	/**
	 * データコピー
	 * @param App\Models\HpEstateSetting $cmsSettingRow
	 * @param App\Models\HpEstateSetting $toSettingRow
	 */
	public function copyTo($cmsSettingRow, $toSettingRow) {
		$fromRowset = $this->getSettingAll($cmsSettingRow->hp_id, $cmsSettingRow->id);
		$toRowset   = $this->getSettingAll($toSettingRow->hp_id, $toSettingRow->id);

		$toSettings = [];
		foreach ($toRowset as $row) {
			if ($row->delete_flg == 0) {
				$toSettings[$row->hp_estate_setting_id][$row->hp_id][$row->estate_class] = $row;
			}
		}

		// 更新対象の既存レコード
		$updatedIdList = [];

		// コピー処理
		foreach ($fromRowset as $row) {
			$data = $row->toArray();
			unset($data['id']);
			unset($data['hp_estate_setting_id']);
			unset($data['update_date']);
			unset($data['create_date']);

			// コピー先の設定IDをセット
			$data['hp_estate_setting_id'] = $toSettingRow->id;
			$data['update_date'] = date('Y-m-d H:i:s');

			// 既存レコードがある場合は更新
			if (isset($toSettings[$toSettingRow->id][$row->hp_id][$row->estate_class])) {
				$newRow = $toSettings[$toSettingRow->id][$row->hp_id][$row->estate_class];
				$data['origin_id'] = $row->origin_id;
				$updatedIdList[] = $newRow->id;
			}
			else {
				$newRow = $this->create();
			}

			$newRow->setFromArray($data);
			$newRow->save();
		}

		// delete
		foreach ($toRowset as $row) {
			if (!in_array($row->id, $updatedIdList)){
				$row->delete();
			}
		}
	}
}