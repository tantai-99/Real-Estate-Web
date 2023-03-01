<?php
namespace Library\Custom\Publish\Special\Prepare;

use App\Traits\FetchSpecialRowset;
use App\Traits\GenerateParams;

class Simple extends PrepareAbstract {

    use FetchSpecialRowset, GenerateParams;

    public function fetchSpecialRowset() {

        // 物件検索データない
        if (!$this->settingCms instanceof \App\Models\HpEstateSetting) {
            return [];
        }

        $rowsetCms = $this->settingCms->getSpecialAllWithPubStatus();
        $unsetList = []; // foreach中にunsetするとズレが生じるので

        // ラベル情報を追加
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

                $unsetList[] = $rowCms->id; // 差分なし
                continue;
            }

            // 公開中データなし && 日付比較
            if (!$rowPublic && $rowCms->update_date <= $rowCms->published_at) {

                $unsetList[] = $rowCms->id; // 差分なし
                continue;
            }

            // 差分あり
            $rowsetCms[$i]->publishStatus = config('constants.special_estate.row.PUBLISH_STATUS_UPDATE');
        }

        // 差分なしunset
        if (count($unsetList) > 0) {
            $rowsetCms->unsetRows($unsetList);
        }

        return $rowsetCms;
    }

}