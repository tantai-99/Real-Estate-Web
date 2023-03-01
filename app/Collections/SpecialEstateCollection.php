<?php

namespace App\Collections;

use Illuminate\Support\Facades\App;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;

class SpecialEstateCollection extends CustomCollection
{

    /**
     * URLを元にレコードを検索する
     * @param string $url
     */
    public function findByUrl($url)
    {

        foreach ($this as $i => $data) {
            if ($data['filename'] == $url) {
                return $data;
            }
        }
        return false;
    }

    /*
     * rowオブジェクトをunset
     */
    public function unsetRow($id)
    {

        foreach ($this as $i => $v) {
            if ((int)$v->id === (int)$id) {
                unset($this[$i]);
                unset($this[$i]);
                break;
            }
        }
    }

    /*
     * rowオブジェクトをunset
     */
    public function unsetRows(array $ids)
    {

        foreach ($this as $i => $row) {
            if (in_array($row->id, $ids)) {
                unset($this[$i]);
            }
        }
    }

    public function findRow($id)
    {

        foreach ($this as $i => $row) {
            if ((int)$row->id === (int)$id) {
                return $row;
            }
        }
        return null;
    }

    /**
     * origin idでrowをフィルター
     *
     * @param $originId
     * @return null|Zend_Db_Table_Row_Abstract
     */
    public function findRowByOrigin($originId)
    {

        foreach ($this as $i => $row) {
            if ((int)$row->origin_id === (int)$originId) {
                return $row;
            }
        }
        return null;
    }

    public function generateParams()
    {

        $res = [];

        $param = [
            'update'          => 1,
            'new_release_flg' => 1,
            'new_release_at'  => 0,
            'new_close_flg'   => 0,
            'new_close_at'    => 0,
        ];

        foreach ($this as $i => $row) {

            if (!$row->is_public) {
                continue;
            }
            $res['special'][$row->id] = $param;
        }
        return $res;
    }

    public function findCmsRow()
    {

        return $this->_findRow(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
    }

    public function findTestRow()
    {

        return $this->_findRow(config('constants.hp_estate_setting.SETTING_FOR_TEST'));
    }

    public function findPublicRow()
    {

        return $this->_findRow(config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
    }

    private function _findRow($settingFor)
    {

        $table = App::make(HpEstateSettingRepositoryInterface::class);

        foreach ($this as $i => $row) {

            $settingRow = $table->find($row->hp_estate_setting_id);
            if ($settingRow && $settingRow->setting_for == $settingFor) {
                return $row;
            }
        }
        return null;
    }

}
