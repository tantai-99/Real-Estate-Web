<?php
namespace Library\Custom\Publish\Special\Prepare;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;

class Testsite extends PrepareAbstract {

    public function reserveList() {

        // 物件検索データない
        if (!$this->settingCms instanceof \App\Models\HpEstateSetting) {
            return [];
        }


        // post
        $params =[];
        if(app('request')->hasSession()) {
            $params = app('request')->session()->get('publish')->params;
        }
        if (isset($params['submit_from']) && $params['submit_from'] == 'simple') {
            return [];
        }

        $rowsetCms = $this->settingCms->getSpecialAllWithPubStatus();

        // 特集予約
        $table = \App::make(ReleaseScheduleSpecialRepositoryInterface::class);

        $rowset = $table->fetchAllReserve($this->hp->id);

        // postもdbの予約もない
        if (!isset($params['special']) && count($rowset) < 1) {
            return [];
        }

        // $res = [Default_Form_Publish::NOW =>[]];
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
            $res[$row->release_at][$row->release_type_code][$row->id] = $rowsetCms->findRow($row->special_estate_id)->title;
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