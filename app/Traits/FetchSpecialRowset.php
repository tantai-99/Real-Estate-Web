<?php
namespace App\Traits;

trait FetchSpecialRowset {

    public function fetchSpecialRowset() {

        // 物件検索データない
        if (!$this->settingCms instanceof \App\Models\HpEstateSetting) {
            return [];
        }

        $rowsetCms = $this->settingCms->getSpecialAllWithPubStatus();

        foreach ($rowsetCms as $i => $rowCms) {

            if (!$this->settingPublic instanceof \App\Models\HpEstateSetting) {
                $rowsetCms[$i]->publishStatus = config('constants.special_estate.row.PUBLISH_STATUS_NEW');
                continue;
            }

            $rowPublic = $this->settingPublic->getSpecialByOriginId($rowCms->id);

            // 公開中データなし && 公開履歴なし
            if ($rowPublic === null && $rowCms->published_at === null) {
                $rowsetCms[$i]->publishStatus = config('constants.special_estate.row.PUBLISH_STATUS_NEW');
                continue;
            }

            // 公開中データあり && 日付比較
            if ($rowPublic && $rowCms->update_date <= $rowPublic->update_date) {

                $rowsetCms[$i]->publishStatus = config('constants.special_estate.row.PUBLISH_STATUS_NO_DIFF');
                continue;
            }

            // 公開中データなし && 日付比較
            if (!$rowPublic && $rowCms->update_date <= $rowCms->published_at) {

                $rowsetCms[$i]->publishStatus = config('constants.special_estate.row.PUBLISH_STATUS_NO_DIFF');
                continue;
            }

            // 差分あり
            $rowsetCms[$i]->publishStatus = config('constants.special_estate.row.PUBLISH_STATUS_UPDATE');

        }

        return $rowsetCms;
    }
}