<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Collections\SpecialEstateCollection;
use Library\Custom\Estate\Setting\Special;
use App\Models\Hp;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepositoryInterface;
use App\Repositories\HpMainParts\HpMainPartsRepository;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use Illuminate\Support\Facades\App;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;

class SpecialEstate extends Model
{
    use MySoftDeletes;

    protected $table = 'special_estate';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    const ESTATE_LINK_TYPE = 'special';
    protected $_canDelete;

    protected $fillable = [
        'id',
        'origin_id',
        'hp_estate_setting_id',
        'hp_id',
        'title',
        'filename',
        'comment',
        'create_special_date',
        'estate_class',
        'enabled_estate_type',
        'owner_change',
        'jisha_bukken',
        'niji_kokoku',
        'niji_kokoku_jido_kokai',
        'only_er_enabled',
        'second_estate_enabled',
        'end_muke_enabled',
        'tesuryo_ari_nomi',
        'tesuryo_wakare_komi',
        'kokokuhi_joken_ari',
        'only_second',
        'exclude_second',
        'map_search_here_enabled',
        'display_freeword',
        'houses_id',
        'area_search_filter',
        'search_filter',
        'method_setting',
        'published_at',
        'first_publish_date',
        'updated_at',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hpEstateSetting()
    {
        return $this->belongsTo(HpEstateSetting::class, 'hp_estate_setting_id');
    }

    public function hp($col) {
        return $this->belongsToMany(Hp::class, $col);
    }

    public function getLinkId()
    {
        return 'estate_' . static::ESTATE_LINK_TYPE . '_' . $this->origin_id;
    }

    public function getTitle($withFilename = false)
    {
        $title = $this->title;
        if ($withFilename) {
            $title .= '（' . $this->getFilename() . '）';
        }
        return $title;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * !公開情報を持っている事
     */
    public function toSiteMapArray()
    {
        $data = [];
        $data['estate_page_type']   = 'estate_' . static::ESTATE_LINK_TYPE;
        $data['id']             = (int)$this->id;
        $data['origin_id']      = (int)$this->origin_id;
        $data['title']          = $this->getTitle();
        $data['parent_page_id'] = null;
        $data['sort']           = 0;
        $data['public_flg']     = (bool)$this->is_public;
        $data['link_id']        = $this->getLinkId();
        $data['filename']       = $this->filename;
        $data['update_date']    = $this->updated_at ? $this->updated_at : $this->create_special_date;
        return $data;
    }

    public function toSettingObject($cache = true) {
	    if (!$cache) {
	        return new Special($this->toArray());
	    }
	    if (!$this->_settingObject) {
	        $this->_settingObject = new Special($this->toArray());
	    }
		return $this->_settingObject;
	}

    public function canDelete($cache = true) {
		if ($cache && !is_null($this->_canDelete)) {
			return $this->_canDelete;
		}

		// 公開中は削除不可
		if (isset($this->is_public)) {
			$isPublic = $this->is_public == 1;
		}
		else {
            $hp=App::make(HpRepositoryInterface::class)->fetchRow([['id',$this->hp_id]]);
			$pubSetting = $hp->getEstateSettingForPublic();
			if ($pubSetting && $pubSetting->getSpecialByOriginId($this->origin_id)) {
				$isPublic = true;
			}
			else {
				$isPublic = false;
			}
		}

		if ($isPublic) {
			return false;
		}

		// 参照している物件コマがある場合は削除不可
		$partsTable = App::make(HpMainPartsRepositoryInterface::class);
		$where = [
			['hp_id',$this->hp_id],
			['parts_type_code',HpMainPartsRepository::PARTS_ESTATE_KOMA],
            [config('constants.estate_koma.SPECIAL_ID_ATTR'),$this->origin_id],
		];
		$usedCount = $partsTable->countRows($where);
		$this->_canDelete = ($usedCount == 0);

		return $this->_canDelete;
	}

    public function newCollection(array $models = Array()) {
		return new SpecialEstateCollection($models);
	}

    /**
     * 公開予約の有無
     * @return bool
     */
    public function isScheduled() {
        return App::make(ReleaseScheduleSpecialRepositoryInterface::class)->hasReserveByHpId($this->hp_id, $this->id);
    }

    public function copySpecial() {
		$table = App::make(SpecialEstateRepositoryInterface::class);
		$row = $table->create([
            'hp_estate_setting_id' => $this->hp_estate_setting_id,
            'estate_class'=>0,
        ]);
		$exclude = [
			'id', 'origin_id', 'create_special_date', 'create_date', 'update_date', 'published_at'
		];
		$copyData = [];
		foreach ($this->toArray() as $col => $val) {
			if (in_array($col, $exclude)) {
				continue;
			}
			$copyData[$col] = $val;
		}
		$row->setFromArray($copyData);
		$row->create_special_date = date('Y-m-d H:i:s');
		$row->filename = $table->getCopyFilename($this->hp_id, $this->hp_estate_setting_id);
		$row->save();
		$row->origin_id = $row->id;
		$row->save();
		return $row;
	}

    /**
     * 公開中かどうか
     * @return bool
     */
    public function isPublic() {
        if (isset($this->is_public)) {
            return true;
        }
        return false;
    }
}