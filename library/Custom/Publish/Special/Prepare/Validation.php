<?php
namespace Library\Custom\Publish\Special\Prepare;

use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;

class Validation extends PrepareAbstract {

    public function reserveList(array $params) {

        // 物件検索データない
        if (!$this->settingCms instanceof \App\Models\HpEstateSetting) {
            return [];
        }

        // post
        //$params = (new Zend_Session_Namespace('publish'))->params;

        $rowsetCms = $this->settingCms->getSpecialAllWithPubStatus();

        $table = \App::make(ReleaseScheduleSpecialRepositoryInterface::class);

        // 特集予約
        $rowset = $table->fetchAllReserve($this->hp->id);

        // postもdbの予約もない
        if (!isset($params['special']) && count($rowset) < 1) {
            return [];
        }

        $res = [];

        // 既存の予約
        foreach ($rowset as $row) {

            // post値を優先
            if (isset($params['special'])) {

                $overwrite = false;
                foreach ($params['special'] as $id => $param) {

                    if ((int)$id === (int)$row->id && $param['update']) {
                        $overwrite = true;
                        break;
                    }
                }
                // postされて場合はDBの予約スルー
                if ($overwrite) {
                    continue;
                }
            }

            // 既存の予約をセット
            $res[$row->release_at][$row->release_type_code]["sp_{$row->special_estate_id}"] = $rowsetCms->findRow($row->special_estate_id)->title;
        }

        // postされた予約
        if (isset($params['special'])) {

            foreach ($params['special'] as $id => $param) {

                // 更新対象外はスルー
                if (!$param['update']) {
                    continue;
                }

                $row = $rowsetCms->findRow($id);

                if (isset($param['new_release_at']) && $param['new_release_at']) {
                    $res[$table->dateForDb($param['new_release_at'])][config('constants.release_schedule.RESERVE_RELEASE')]["sp_{$id}"] = "{$row->title}（{$row->filename}）";
                }

                if (isset($param['new_close_at']) && $param['new_close_at']) {
                    $res[$table->dateForDb($param['new_close_at'])][config('constants.release_schedule.RESERVE_CLOSE')]["sp_{$id}"] = "{$row->title}（{$row->filename}）";
                }
            }
        }

        return $res;
    }
}