<?php
namespace Library\Custom\Publish\Special\Make;

use App\Http\Form;

class Rowset {

    public $estateSettingCms;
    public $estateSettingPublic;
    public $rowsetCms;
    public $rowsetPublic;

    private static $instance;

    private function __construct() {
    }

    public static function getInstance() {

        if (!self::$instance) self::$instance = new static;
        return self::$instance;
    }

    /**
     * 特集をcurrentAtの状態に更新する
     *
     * @param                          $hp
     * @param array                    $params
     * @param int                      $currentAt
     * @param array                    $reserveList
     */
    public function init($hp, array $params, $currentAt = Form\Publish::NOW, array $reserveList, $batch = false) {

        // reset property
        foreach (get_object_vars($this) as $property => $value) {
            $this->$property = null;
        }

        // set special params
        $paramsSpecial = $this->fileterSpecialParams($params);

        // fetch setting and rowset
        $this->estateSettingCms = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_CMS'));
        $this->rowsetCms        = $this->estateSettingCms instanceof \App\Models\HpEstateSetting ? $this->estateSettingCms->getSpecialAllWithPubStatus() : [];

        $this->estateSettingPublic = $hp->getEstateSetting(config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'));
        $this->rowsetPublic        = $this->estateSettingPublic instanceof \App\Models\HpEstateSetting ? $this->estateSettingPublic->getSpecialAllWithPubStatus() : [];
        $this->reserveList = [];

        // no data
        if (!$this->rowsetCms instanceof \App\Collections\SpecialEstateCollection) {
            return;
        }

        if (count($reserveList) < 1) {
            $reserveList = [Form\Publish::NOW => null];
        }
        else {
            array_unshift($reserveList, [Form\Publish::NOW => null]);
        }

        // 日時ごとに更新
        foreach ($reserveList as $releaseAt => $reserveListWhenReleaseAt) {

            // 予約日時以前であればループ抜ける
            if ($currentAt < $releaseAt) {
                break;
            }

            // post
            // 現在時刻のみ
            if ($releaseAt === Form\Publish::NOW) {

                if (!is_array($paramsSpecial)) {
                    continue;
                }

                // cmsのrowsetに更新情報を反映させる
                foreach ($this->rowsetCms as $i => $row) {

                    $this->rowsetCms[$i]->updateNow = false;

                    // 更新対象外はcontinue
                    if (!array_key_exists($this->rowsetCms[$i]->id, $paramsSpecial) || !$paramsSpecial[$row->id]['update']) {
                        continue;
                    }

                    // 以下、更新対象
                    $this->rowsetCms[$i]->updateNow = true;

                    // 簡易設定
                    if (!isset($paramsSpecial[$row->id]['new_release_flg'])) {
                        $this->rowsetCms[$i]->is_public = (int)true;
                        continue;
                    }

                    if (!$batch && $currentAt == Form\Publish::NOW) {
                        if ($paramsSpecial[$row->id]['new_release_flg'] && $paramsSpecial[$row->id]['new_release_at'] != $currentAt) {
                            $this->rowsetCms[$i]->updateNow = false;
                            $this->reserveList[] = $this->rowsetCms[$i]->origin_id;
                            continue;
                        }
                        if (isset($paramsSpecial[$row->id]['new_close_flg']) && $paramsSpecial[$row->id]['new_close_flg'] && $paramsSpecial[$row->id]['new_close_at'] != $currentAt) {
                            $this->rowsetCms[$i]->updateNow = false;
                            $this->reserveList[] = $this->rowsetCms[$i]->origin_id;
                            continue;
                        }
                    }

                    // 詳細設定
                    if ($paramsSpecial[$row->id]['new_release_flg'] && $paramsSpecial[$row->id]['new_release_at'] == Form\Publish::NOW) {

                        // release now
                        $this->rowsetCms[$i]->is_public = (int)true;
                        continue;
                    }

                    if (isset($paramsSpecial[$row->id]['new_close_flg']) && $paramsSpecial[$row->id]['new_close_flg'] && $paramsSpecial[$row->id]['new_close_at'] == Form\Publish::NOW) {

                        // close now
                        $this->rowsetCms[$i]->is_public = (int)false;
                        continue;
                    }
                }
                continue;
            }

            // reserve
            foreach ($reserveListWhenReleaseAt as $releaseOrClose => $array) {

                foreach ($array as $id => $title) {

                    // 通常ページの予約は弾く
                    if (!preg_match('/^sp_/', $id)) {
                        continue;
                    };

                    $id = (int)str_replace('sp_', '', $id);

                    foreach ($this->rowsetCms as $i => $row) {
                        if ((int)$this->rowsetCms[$i]->id !== $id) {
                            continue;
                        }
                        $this->rowsetCms[$i]->updateNow = true;
                        if ((int)$releaseOrClose === config('constants.release_schedule.RESERVE_RELEASE')) {
                            $this->rowsetCms[$i]->is_public = (int)true;
                            continue;
                        }
                        if ((int)$releaseOrClose === config('constants.release_schedule.RESERVE_CLOSE')) {
                            $this->rowsetCms[$i]->is_public = (int)false;
                            continue;
                        }
                    }
                }
            }
        }
    }

    public function filterRowByOriginId($originId) {

        foreach ($this->rowsetCms as $row) {

            if ((int)$originId === (int)$row->origin_id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * 公開中の特集のIDを取得
     *
     * @return array
     */
    public function filterPublicIds() {

        $res = [];

        foreach ($this->rowsetCms as $row) {

            if ($row->is_public) {
                $res[] = $row->id;
            }
        }

        return $res;
    }

    /**
     * 公開中 && 更新対象の特集のIDを取得
     *
     * @return array
     */
    public function filterPublicIdsUpdateNow() {

        $res = [];

        foreach ($this->rowsetCms as $row) {

            if ($row->is_public && $row->updateNow) {
                $res[] = $row->id;
            }
        }

        return $res;
    }

    /**
     * 特集ページのPostされた値のみを取得
     *
     * @param $params
     * @return array|null
     */
    private function fileterSpecialParams($params) {

        $res = null;
        if (isset($params['special'])) {

            $res = [];
            foreach ($params['special'] as $id => $param) {

                $id = (int)$id;

                $res[$id]['update'] = (bool)$param['update'];

                if (isset($param['new_release_flg'])) {
                    $res[$id]['new_release_flg'] = (bool)$param['new_release_flg'];
                }
                else {
                    if (!($res[$id]['update'] && isset($params['submit_from']) && $params['submit_from'] == 'simple')) {
                        $res[$id]['new_release_flg'] = false;
                    }
                }
                if (isset($param['new_release_at'])) {
                    $res[$id]['new_release_at'] = $param['new_release_at'];
                } else {
                    $res[$id]['new_release_at'] = 0;
                }
                if (isset($param['new_close_flg'])) {
                    $res[$id]['new_close_flg'] = (bool)$param['new_close_flg'];
                }else {
                    $res[$id]['new_close_flg'] = 0;
                }
                if (isset($param['new_close_at'])) {
                    $res[$id]['new_close_at'] = $param['new_close_at'];
                }else {
                    $res[$id]['new_close_at'] = 0;
                }
            }
        }
        return $res;
    }

}