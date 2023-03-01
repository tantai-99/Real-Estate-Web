<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;
use App\Repositories\EstateClassSearch\EstateClassSearchRepositoryInterface;
use App\Repositories\SpecialEstate\SpecialEstateRepositoryInterface;
use Library\Custom\Model\Estate\TypeList;
use App\Repositories\HpPage\HpPageRepositoryInterface;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use Library\Custom\Model\Estate\ClassList;

class HpEstateSetting extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_estate_setting';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    const ESTATE_LINK_TYPE = 'top';
    const ESTATE_RENT_LINK_TYPE = 'rent';
    const ESTATE_PURCHASE_LINK_TYPE = 'purchase';

    protected $fillable = [
        'id',
        'hp_id',
        'setting_for',
        'updated_at',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hp($col)
    {
        return $this->belongsTo(Hp::class, $col);
    }

    public function specialEstate()
    {
        return $this->hasMany(SpecialEstate::class, 'hp_estate_setting_id');
    }

    public function getRepository()
    {
        return App::make(HpEstateSettingRepositoryInterface::class);
    }

    /**
     * 物件種別毎の物件検索設定を全て取得する(公開ステータス付き)
     */
    public function getSearchSettingAllWithPubStatus()
    {
        $table = App::make(EstateClassSearchRepositoryInterface::class);
        return $table->getSettingAllWithPubStatus($this->hp_id, $this->id);
    }

    /**
     * 特集一覧を取得する(公開ステータス付き)
     */
    public function getSpecialAllWithPubStatus($orderOption = null)
    {
        $table = App::make(SpecialEstateRepositoryInterface::class);
        return $table->fetchAllWithPubStatus($this->hp_id, $this->id, $orderOption);
    }

    public function getSearchSettingAll()
    {
        $table = App::make(EstateClassSearchRepositoryInterface::class);
        return $table->getSettingAll($this->hp_id, $this->id);
    }

    public function saveSearchSetting($setting)
    {
        $table = App::make(EstateClassSearchRepositoryInterface::class);
        return $table->saveSetting($this->hp_id, $this->id, $setting);
    }

    public function getLinkId($title)
    {
        switch ($title) {
            case "物件検索トップ":
                return 'estate_' . static::ESTATE_LINK_TYPE;
                break;
            case "賃貸物件検索トップ":
                return 'estate_' . static::ESTATE_RENT_LINK_TYPE;
                break;
            case "売買物件検索トップ":
                return 'estate_' . static::ESTATE_PURCHASE_LINK_TYPE;
                break;
        }
    }

    public function getTitle($title, $filename, $withFilename = false)
    {
        if ($withFilename) {
            $title .= '（' . $filename . '）';
        }
        return $title;
    }

    public function getSpecialAll($order = null)
    {
        $table = App::make(SpecialEstateRepositoryInterface::class);
        return $table->fetchSpecialAll($this->hp_id, $this->id, $order);
    }

    public function getSearchSetting($class)
    {
        $table = App::make(EstateClassSearchRepositoryInterface::class);
        return $table->getSetting($this->hp_id, $this->id, $class);
    }

    /**
	 * 本番未反映の設定・特集があるかどうか
	 * @return boolean
	 */
	public function hasChanged() {
		return $this->getRepository()->hasChanged($this->hp_id, $this->id);
	}

	public function cmsUpdated() {
	    $table = App::make(HpEstateSettingRepositoryInterface::class);
	    $table->cmsUpdated($this->hp_id);
    }
    
    public function cmsLastUpdated() {
	    $table = App::make(HpEstateSettingRepositoryInterface::class);
	    $table->cmsLastUpdated($this->hp_id);
	}

	/**
	 * 物件関連公開処理 本番
	 * @param array $specialIds
	 */
	public function copyToPublic($specialIds = []) {
	    $table = App::make(HpEstateSettingRepositoryInterface::class);
	    $table->copyToPublic($this, $specialIds);
	}

	/**
	 * 物件関連公開処理 テストサイト
	 * @param array $specialIds
	 */
	public function copyToTest($specialIds = [], $reserveList = []) {
	    $table = App::make(HpEstateSettingRepositoryInterface::class);
	    $table->copyToTest($this, $specialIds, $reserveList);
	}

    public function toSiteMapData() {

        $res = [];
        $statusPublics = [];

        // 物件検索トップ
        $res['top'] = $this->toSiteMapArray("物件検索トップ", "shumoku");

        // 物件種目
        $res['estateTypes'] = [];
        foreach ($this->getSearchSettingAllWithPubStatus() as $estateClassSearchRow) {
            $res['estateTypes'] = array_merge($res['estateTypes'], $estateClassSearchRow->toSiteMapArray());
        }

        //物件種目の設定があれば、該当するトップを取得
        foreach ($res['estateTypes'] as $estate_type) {
            if (
                $estate_type['estate_class'] == ClassList::CLASS_CHINTAI_KYOJU ||
                $estate_type['estate_class'] == ClassList::CLASS_CHINTAI_JIGYO
            ) {
                // 賃貸物件検索トップ
                $res['chintai_top'] = $this->toSiteMapArray("賃貸物件検索トップ", "rent");
                $res['chintai_top']['update_date'] = $this->getUpdateDateEstateTop($res['estateTypes'], 'chintai_top');
                $statusPublics[] = $res['chintai_top']['public_flg'] = $this->getPublicFlgEstateTop($res['estateTypes'], 'chintai_top');
            } elseif (
                $estate_type['estate_class'] == ClassList::CLASS_BAIBAI_KYOJU ||
                $estate_type['estate_class'] == ClassList::CLASS_BAIBAI_JIGYO
            ) {
                // 売買物件検索トップ
                $res['baibai_top'] = $this->toSiteMapArray("売買物件検索トップ", "purchase");
                $res['baibai_top']['update_date'] = $this->getUpdateDateEstateTop($res['estateTypes'], 'baibai_top');
                $statusPublics[] = $res['baibai_top']['public_flg'] = $this->getPublicFlgEstateTop($res['estateTypes'], 'baibai_top');
            }
        }

        // 特集
        $res['specials'] = [];
        foreach ($this->getSpecialAllWithPubStatus() as $specialRow) {
            $res['specials'][] = $specialRow->toSiteMapArray();
        }

        $res['estateTypes'] = array_values($res['estateTypes']);

        $res['top']['public_flg'] = in_array(true, $statusPublics) ? true : false;

        if (is_null($res['top']['update_date'])) {
            $res['top']['update_date'] = $this->create_date;
            $updates = array();
            if (isset($res['chintai_top'])) {
                $updates[] = $res['chintai_top']['update_date'];
            }
            if (isset($res['baibai_top'])) {
                $updates[] = $res['baibai_top']['update_date'];
            }
            if (count($updates) > 0) {
                $res['top']['update_date'] = max($updates);
            }
        }

        if (App::make(HpPageRepositoryInterface::class)->isPublicToppage($this->hp_id)) {
            return $res;
        }

        // トップページが非公開の場合、物件検索、特集は非公開の表示
        $res['top']['public_flg'] = false;
        $res['chintai_top']['public_flg'] = false;
        $res['baibai_top']['public_flg'] = false;

        foreach ($res['estateTypes'] as $i => $v) {
            $res['estateTypes'][$i]['public_flg'] = false;
        }

        foreach ($res['specials'] as $i => $v) {
            $res['specials'][$i]['public_flg'] = false;
        }

        return $res;
    }

    public function toSiteMapArray($title, $filename)
    {
        $data = [];
        $data['estate_page_type']   = $this->getLinkId($title);
        $data['id']                 = 0;
        $data['title']              = $this->getTitle($title, $filename);
        $data['parent_page_id']     = null;
        $data['sort']               = 0;
        $data['public_flg']         = $this->isPublished();
        $data['link_id']            = $this->getLinkId($title);
        $data['filename']           = $filename;
        $data['update_date']        = $this->updated_at;
        return $data;
    }

    public function isPublished()
    {
        $settingTable = App::make(HpEstateSettingRepositoryInterface::class);
        return !!$settingTable->getSettingForPublic($this->hp_id);
    }

    public function getUpdateDateEstateTop($estateTypes, $type)
    {
        $updateDates = array(0 => 0);
        if ($type == "chintai_top") {
            $chintaiKyojuClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_CHINTAI_KYOJU;
            });
            if (count($chintaiKyojuClass) > 0) {
                $chintaiKyojuClass = array_values($chintaiKyojuClass);
                $updateDates[$chintaiKyojuClass[0]['update_date']] =  strtotime($chintaiKyojuClass[0]['update_date']);
            }
            $chintaiJigyoClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_CHINTAI_JIGYO;
            });
            if (count($chintaiJigyoClass) > 0) {
                $chintaiJigyoClass = array_values($chintaiJigyoClass);
                $updateDates[$chintaiJigyoClass[0]['update_date']] =  strtotime($chintaiJigyoClass[0]['update_date']);
            }
        } elseif ($type == "baibai_top") {
            $baibaiKyojuClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_BAIBAI_KYOJU;
            });
            if (count($baibaiKyojuClass) > 0) {
                $baibaiKyojuClass = array_values($baibaiKyojuClass);
                $updateDates[$baibaiKyojuClass[0]['update_date']] =  strtotime($baibaiKyojuClass[0]['update_date']);
            }
            $baibaiJigyoClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_BAIBAI_JIGYO;
            });
            if (count($baibaiJigyoClass) > 0) {
                $baibaiJigyoClass = array_values($baibaiJigyoClass);
                $updateDates[$baibaiJigyoClass[0]['update_date']] =  strtotime($baibaiJigyoClass[0]['update_date']);
            }
        }
        return array_search(max($updateDates), $updateDates);
    }

    public function getPublicFlgEstateTop($estateTypes, $type)
    {
        $publicFlg = false;
        if ($type == "chintai_top") {
            $chintaiKyojuClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_CHINTAI_KYOJU;
            });
            if (count($chintaiKyojuClass) > 0 && array_values($chintaiKyojuClass)[0]['public_flg']) {
                $publicFlg = true;
            }
            $chintaiJigyoClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_CHINTAI_JIGYO;
            });
            if (count($chintaiJigyoClass) > 0 && array_values($chintaiJigyoClass)[0]['public_flg']) {
                $publicFlg = true;
            }
        } elseif ($type == "baibai_top") {

            $baibaiKyojuClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_BAIBAI_KYOJU;
            });
            if (count($baibaiKyojuClass) > 0 && array_values($baibaiKyojuClass)[0]['public_flg']) {
                $publicFlg = true;
            }
            $baibaiJigyoClass = array_filter($estateTypes, function ($value) {
                return $value['estate_class'] == ClassList::CLASS_BAIBAI_JIGYO;
            });
            if (count($baibaiJigyoClass) > 0 && array_values($baibaiJigyoClass)[0]['public_flg']) {
                $publicFlg = true;
            }
        }
        return $publicFlg;
    }

    public function getSearchSettingAllForFreeword()
    {
        $table = App::make(EstateClassSearchRepositoryInterface::class);
        return $table->getSettingAllForFreeword($this->hp_id, $this->id);
    }

    public function saveSpecial($setting,$row = null) {
		$table = App::make(SpecialEstateRepositoryInterface::class);
		if ($row) {
            
			return $table->updateSetting($row, $setting);
		}
		else {
			return $table->createSetting($this->hp_id, $this->id, $setting);
		}
	}

    public function getSpecialWithPubStatus($id) {
		$table = App::make(SpecialEstateRepositoryInterface::class);
		return $table->fetchWithPubStatus($this->hp_id, $this->id, $id);
	}

    public function getSpecialByOriginId($originId) {
		$table = App::make(SpecialEstateRepositoryInterface::class);
		return $table->fetchSpecialByOriginId($this->hp_id, $this->id, $originId);
	}

    public function getSpecial($id) {
		$table = App::make(SpecialEstateRepositoryInterface::class);
		return $table->fetchSpecial($this->hp_id, $this->id, $id);
	}

    public function getSpecialAllWithPubStatusByCond($col,$cond,$orderOption = null) {
        $table = App::make(SpecialEstateRepositoryInterface::class);
        return $table->fetchAllWithPubStatusByCond($this->hp_id, $this->id,$orderOption,$col,$cond);
    }

    public function getSearchSettingByEstateType($type) {
        $table = App::make(EstateClassSearchRepositoryInterface::class);
        return $table->getSettingByEstateType($this->hp_id, $this->id, $type);
    }
}