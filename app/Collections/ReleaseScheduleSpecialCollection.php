<?php

namespace App\Collections;

use Illuminate\Support\Facades\App;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;

class ReleaseScheduleSpecialCollection extends CustomCollection
{
    public function parseToParams()
    {

        return $this->_parseToParamsAbstract();
    }

    public function parseToParamsPre()
    {

        return $this->_parseToParamsAbstract(true);
    }

    private function _parseToParamsAbstract($isPre = false)
    {

        $_PRE = '';
        if ($isPre) {
            $_PRE = '_PRE';
        }

        $res = [];

        $table = App::make(ReleaseScheduleSpecialRepositoryInterface::class);

        foreach ($this as $row) {

            $id = $row->special_estate_id;

            // init
            if (!isset($res["special_{$id}_update"])) {

                $res["special_{$id}_new_release_flg"] = 0;
                $res["special_{$id}_new_release_at"]  = 0;
                $res["special_{$id}_new_close_flg"]   = 0;
                $res["special_{$id}_new_close_at"]    = 0;
            }

            // update
            $res["special_{$id}_update"] = 1;

            // release
            if ($row->release_type_code == config("constants.release_schedule.RESERVE_RELEASE{$_PRE}")) {
                $res["special_{$id}_new_release_flg"] = 1;
                $res["special_{$id}_new_release_at"]  = $row->release_at ? $table->dateForApp($row->release_at) : 0;
            }

            // close
            if ($row->release_type_code == config("constants.release_schedule.RESERVE_CLOSE{$_PRE}")) {
                $res["special_{$id}_new_close_flg"] = 1;
                $res["special_{$id}_new_close_at"]  = $row->release_at ? $table->dateForApp($row->release_at) : 0;
            }
        }
        return $res;
    }
}
