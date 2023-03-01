<?php
namespace App\Repositories\HpEstateSetting;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use Illuminate\Support\Facades\App;

class HpEstateSettingRepository extends BaseRepository implements HpEstateSettingRepositoryInterface
{

	protected $_name = 'hp_estate_setting';
    public function getModel()
    {
        return \App\Models\HpEstateSetting::class;
    }

    public function getSetting($hpId, $settingFor = null) {
        if (!$settingFor) {
            $settingFor = config('constants.hp_estate_setting.SETTING_FOR_CMS');
        }
        
        $params = [
            ['hp_id', $hpId],
            ['setting_for', $settingFor],
        ];
        return $this->fetchRow($params);
    }
	
    public function getSettingForPublic($hpId) {
        return $this->getSetting($hpId, config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
    }

    /**
	 * 物件設定を作成する
	 */
	public function createSetting($hpId) {
		$row = $this->create([
			'hp_id' => $hpId,
			'setting_for' => config('constants.hp_estate_setting.SETTING_FOR_CMS'),
            'updated_at' => date('Y-m-d H:i:s'),
		]);
		$row->save();
		return $row;
	}
	
	/**
	 * 本番未反映の設定・特集があるか
	 * @param int $hpId
	 * @param int $cmsSettingId
	 */
	public function hasChanged($hpId, $cmsSettingId) {
        $select = $this->model->withoutGlobalScopes()->from($this->_name . ' AS cms');
        $select->join($this->_name.' AS pub', function($join) use ($cmsSettingId) {
			$join->on('cms.hp_id', 'pub.hp_id')
            ->where('cms.setting_for', config('constants.hp_estate_setting.SETTING_FOR_CMS'))
            ->where('cms.id', $cmsSettingId)
            ->whereRaw('cms.update_date <= pub.update_date');
        });
	    
	    $select->where('pub.hp_id', $hpId);
	    $select->where('pub.setting_for', config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
	    $select->where('pub.delete_flg', 0);
		$select->where('cms.delete_flg', 0);

		$pubSetting = $select->first();
		// CMS設定用レコード更新日時以降の更新日時を持つ公開設定レコードが無い場合、変更あり
		if (!$pubSetting) {
		    return true;
		}
		
		$searchSettingTable = \App::make(EstateClassSearchRepositoryInterface::class);
		$specialTable = \App::make(SpecialEstateRepositoryInterface::class);
		return $searchSettingTable->hasChanged($hpId, $cmsSettingId, $pubSetting->id) ||
					$specialTable->hasChanged($hpId, $cmsSettingId, $pubSetting->id);
	}
	
	/**
	 * CMSレコード更新日付更新
	 */
	public function cmsUpdated($hpId) {
	    $this->update([['hp_id', $hpId], ['setting_for', config('constants.hp_estate_setting.SETTING_FOR_CMS')]], ['update_date' => date('Y-m-d H:i:s')]);
    }
    
    public function cmsLastUpdated($hpId) {
	    $this->update([['hp_id', $hpId], ['setting_for', config('constants.hp_estate_setting.SETTING_FOR_CMS')]], ['updated_at' => date('Y-m-d H:i:s')]);
	}
	
	public function copyToPublic($cmsSettingRow, $specialIds = []) {
	    $this->copyToSettingFor(config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'), $cmsSettingRow, $specialIds);
	}
	
	public function copyToTest($cmsSettingRow, $specialIds = [], $reserveList = []) {
	    $this->copyToSettingFor(config('constants.hp_estate_setting.SETTING_FOR_TEST'), $cmsSettingRow, $specialIds, $reserveList);
	}
	
	protected function copyToSettingFor($settingFor, $cmsSettingRow, $specialIds = [], $reserveList = []) {
		// コピー先設定取得
	    $toSettingRow = $this->getSetting($cmsSettingRow->hp_id, $settingFor);
	    if (!$toSettingRow) {
	    	// 無い場合は作成
	        $toSettingRow = $this->create([
	        	'hp_id' => $cmsSettingRow->hp_id,
	        	'setting_for' => $settingFor,
	        ]);
	    }
	    else {
	    	$toSettingRow->update_date = date('Y-m-d H:i:s');
	    }
	    $toSettingRow->save();
	    
	    // 物件設定のコピー
	    $table = App::make(EstateClassSearchRepositoryInterface::class);
	    $table->copyTo($cmsSettingRow, $toSettingRow);
	    
	    // 特集のコピー
	    $table = App::make(SpecialEstateRepositoryInterface::class);
	    $table->copyTo($cmsSettingRow, $toSettingRow, $specialIds, $reserveList);
	}
}