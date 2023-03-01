<?php
namespace App\Traits;

trait GenerateParams {

    public function generateParams() {

        // 物件検索データない
        if (!$this->settingCms instanceof \App\Models\HpEstateSetting) {
            return [];
        }

        return $this->settingCms->getSpecialAllWithPubStatus()->generateParams();
    }
}