<?php
namespace Library\Custom\Publish\Estate\Prepare;

use App\Models;
use App;
use App\Repositories\HpPage\HpPageRepositoryInterface;

abstract class PrepareAbstract {

    protected $hp;
    public    $settingCms;
    public    $settingPublic;

    public function __construct($hp) {

        $this->hp = $hp;

        $this->settingCms    = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $this->settingPublic = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
    }

    /**
     * 物件検索の表示 or 非表示 判定
     *
     * @return bool
     */
    public function isDisplayEstateSetting() {

        // 物件検索データない
        if (!$this->settingCms instanceof Models\HpEstateSetting) {
            return false;
        }

        // 物件検索非公開
        if (!$this->settingPublic instanceof Models\HpEstateSetting) {
            return true;
        }

        // 物件検索設定の差分確認
        // CMSのデータを基準に公開中データと比較
        foreach ($this->settingCms->getSearchSettingAllWithPubStatus() as $cmsSearchSetting) {
            $pubSearchSetting = $this->settingPublic->getSearchSetting($cmsSearchSetting->estate_class);
            if (is_null($pubSearchSetting) || $pubSearchSetting->update_date < $cmsSearchSetting->update_date) {
                return true;
            }
        }

        // 公開中データを基準にCMSデータと比較
        foreach ($this->settingPublic->getSearchSettingAll() as $pubSearchSetting) {
            $cmsSearchSetting = $this->settingCms->getSearchSetting($pubSearchSetting->estate_class);
            if (is_null($cmsSearchSetting) || $pubSearchSetting->update_date < $cmsSearchSetting->update_date) {
                return true;
            }
        }

        // 物件お問い合わせに差分あり
        return count(App::make(HpPageRepositoryInterface::class)->fetchEstateContactPageAll($this->hp->id, false, true)) > 0;
    }

}