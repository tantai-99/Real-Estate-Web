<?php
namespace Library\Custom\Publish\Special\Prepare;

abstract class PrepareAbstract {

    protected $hp;
    public    $settingCms;
    public    $settingPublic;

    public function __construct($hp) {

        $this->hp = $hp;

        $this->settingCms    = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $this->settingPublic = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
    }

}