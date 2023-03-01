<?php
namespace App\Repositories\SecondEstateClassSearch;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class SecondEstateClassSearchRepository extends BaseRepository implements SecondEstateClassSearchRepositoryInterface
{
    use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\SecondEstateClassSearch::class;
    }
public function getSettingAll($hpId) {
		$params = [
			'hp_id', $hpId,
		];
		return $this->fetchAll([$params], ['estate_class']);
	}
	
	/**
	 * 物件種別毎の検索条件を取得する
	 * @param int $hpId
	 * @param int $class
	 * @return App\Models\EstateClassSearch
	 */
	public function getSetting($hpId, $class) {
		$params = [
			['hp_id', $hpId],
			['estate_class', $class]
		];
		return $this->fetchRow($params);
	}

	/**
	 * 検索条件を取得する
	 * @param int $hpId
	 */
	public function getSettingRow($hpId) {
		$params = [
			['hp_id', $hpId],
		];
		return $this->fetchRow($params);
	}
	
	/**
	 * 物件種別毎の検索条件を保存する
	 */
	public function saveSetting($hpId, $setting) {
		$row = $this->getSetting($hpId, $setting->estate_class);
		if (!$row) {
			$row = $this->create([
				'hp_id'=> $hpId,
				'estate_class' => $setting->estate_class,
			]);
		}

		$row->setFromArray($setting->toSaveData());
		return $row->save();
	}
}