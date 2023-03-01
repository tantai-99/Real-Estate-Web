<?php

namespace App\Repositories\SpecialEstate;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;
use Illuminate\Support\Facades\App;
use App\Repositories\HpEstateSetting\HpEstateSettingRepositoryInterface;
use Library\Custom\Publish\Special\Make\Rowset as Special_Make_Rowset;


class SpecialEstateRepository extends BaseRepository implements SpecialEstateRepositoryInterface
{   
    protected $_integrityCheck = true;
    protected $_name = 'special_estate';
    public function getModel()
    {
        return \App\Models\SpecialEstate::class;
    }

    protected function _createSelectWithPubStatus($calcFoundRows, $hpId, $settingId, $orderOption = null, $count = null, $offset = null) {
        $settingTable = App::make(HpEstateSettingRepositoryInterface::class);
        $pubSetting = $settingTable->getSettingForPublic($hpId);
        if ($pubSetting) {
            $pubSettingId = $pubSetting->id;
        }
        else {
            $pubSettingId = 0;
        }
        // $select = $this->select()->setIntegrityCheck(false);
        // $specialEstate = DB::table('special_estate AS cms');
        $specialEstate = $this->model->withoutGlobalScopes()->from('special_estate AS cms');

        $cols = [];
        if ($calcFoundRows) {
            // $cols[] = new Zend_Db_Expr('SQL_CALC_FOUND_ROWS cms.*');
            $specialEstate->selectRaw('SQL_CALC_FOUND_ROWS cms.*');
        }
        else {
            // $cols[] = 'cms.*';
            $specialEstate->selectRaw('cms.*');
        }
        // $select->from(['cms'=>$this->_name], $cols);

        // $joinCond = $this->_createCmsJoinPubCondition($pubSettingId);
        // $joinCols = [new Zend_Db_Expr('IFNULL(cms.update_date <= pub.update_date, 0) as pub_status, pub.id IS NOT NULL as is_public')];
        $specialEstate->leftJoin('special_estate AS pub', function($join) use ($pubSettingId) {
            $join->on('cms.origin_id', 'pub.origin_id')->where('pub.hp_estate_setting_id', $pubSettingId)->on('cms.hp_id', 'pub.hp_id')->where('pub.delete_flg', 0);
        });
        // $specialEstate->leftJoin('special_estate AS pub', DB::raw($joinCond));
        $specialEstate->selectRaw('IFNULL(cms.update_date <= pub.update_date, 0) as pub_status, pub.id IS NOT NULL as is_public');
        // $select->joinLeft(['pub'=>$this->_name], $joinCond, $joinCols);

        $specialEstate->where('cms.hp_id', $hpId);
        $specialEstate->where('cms.hp_estate_setting_id', $settingId);
        $specialEstate->where('cms.delete_flg', 0);
        $imploded_strings = implode(",", $this->toOrderByFromSortOption($orderOption));
        $specialEstate->orderByRaw($imploded_strings);

        if ($count !== null || $offset !== null) {
            // $select->limit($count, $offset);
            $specialEstate->skip($offset)->take($count);
        }

        return $specialEstate;
    }

    private function _createCmsJoinPubCondition($pubSettingId) {
        return implode(' AND ', [
            'cms.origin_id = pub.origin_id',
            'pub.hp_estate_setting_id = '.$pubSettingId,
            'cms.hp_id = pub.hp_id',
            'pub.delete_flg = 0'
        ]);
    }

    /**
     * 公開ステータス付きデータをすべて取得する
     */
    public function fetchAllWithPubStatus($hpId, $settingId, $orderOption, $count = null, $offset = null) {
        $select = $this->_createSelectWithPubStatus(true, $hpId, $settingId, $orderOption, $count, $offset);

        $rowset = $select->withoutGlobalScopes()->get();

//      $res = $this->getAdapter()->query('SELECT FOUND_ROWS() as cnt');
//      $row = $res->fetch();
//      $rowset->setFoundRows((int) $row['cnt']);
        $rowset->setFoundRows((int) count($rowset));

        return $rowset;
    }


    /**
     * 公開ステータス付きデータを条件を設定し取得する
     */
    public function fetchAllWithPubStatusByCond($hpId, $settingId, $orderOption,$col,$cond, $count = null, $offset = null) {
        $select = $this->_createSelectWithPubStatus(true, $hpId, $settingId, $orderOption, $count, $offset);
        $select->where('cms.'.$col, $cond);
        $rowset =$select->withoutGlobalScopes()->get();
        $rowset->setFoundRows((int) $this->getFoundRow($select));

        return $rowset;
    }

    /**
     * 公開ステータス付きデータを取得する
     */
    public function fetchWithPubStatus($hpId, $settingId, $id) {
        $select = $this->_createSelectWithPubStatus(false, $hpId, $settingId);
        $select->where('cms.id', $id);

        $row = $select->withoutGlobalScopes()->first();
        return $row;
    }

    /**
     * 特集データを取得する
     * @param int $hpId
     * @param int $settingId
     * @param int $id
     * @return App\Models\SpecialEstate
     */
    public function fetchSpecial($hpId, $settingId, $id) {
        return $this->fetchRow([
            ['id',$id],
            ['hp_id',$hpId],
            ['hp_estate_setting_id',$settingId],
        ]);
    }

    /**
     * 特集データを取得する
     * @param int $hpId
     * @param int $settingId
     * @return App\Models\SpecialEstate
     */
    public function fetchSpecialAll($hpId, $settingId, $order = null) {
        return $this->fetchAll([
            ['hp_id', $hpId],
            ['hp_estate_setting_id', $settingId],
        ], $order);
    }

    public function fetchSpecialByCond($hpId, $settingId, $order = null,$cond) {
        return $this->fetchAll([
            ['hp_id', $hpId],
            ['hp_estate_setting_id', $settingId],
            ['enabled_estate_type', $cond],
        ], $order);
    }

    /**
     * ファイル名を指定して特集データを取得する
     * @param int $hpId
     * @param int $settingId
     * @param string $filename
     * @return App\Models\SpecialEstate
     */
    public function fetchSpecialByFilename($hpId, $settingId, $filename) {
        return $this->fetchRow([
            ['filename', $filename],
            ['hp_id', $hpId],
            ['hp_estate_setting_id', $settingId],
            ]);
    }

    /**
     * origin_idを指定して特集データを取得する
     * @param int $hpId
     * @param int $settingId
     * @param int $originId
     * @return App\Models\SpecialEstate
     */
    public function fetchSpecialByOriginId($hpId, $settingId, $originId) {
        return $this->fetchRow([
            ['origin_id', $originId],
            ['hp_id', $hpId],
            ['hp_estate_setting_id', $settingId],
            ]);
    }

    /**
     * 一意なfilenameかチェックする
     */
    public function isUniqueFilename($filename, $hpId, $settingId, $specialId = null) {
        $where = [
            ['hp_id', $hpId],
            ['hp_estate_setting_id', $settingId],
            ['filename', $filename],
        ];
        if ($specialId) {
            $where[] = ['id', '!=', $specialId];
        }
        return !$this->fetchRow($where);
    }

    /**
     * 設定を保存する
     * @param int $hpId
     * @param int $settingId hp_estate_setting.id
     * @param Library\Custom\Estate\Setting\Special $setting
     */
    public function createSetting($hpId, $settingId, $setting) {
        
        $row = $this->create([
            'hp_id' => $hpId,
            'hp_estate_setting_id' => $settingId,
            'create_special_date' => date('Y-m-d H:i:s'),
            'updated_at'           => date('Y-m-d H:i:s'),
            'estate_class'=>0,
        ]);
        $setting->id=$row->id;
        $row->setFromArray($setting->toSaveData());
        $row->save();
        $row->origin_id = $row->id;
        $row->save();
        return $row->id;
    }

    /**
     * 設定を保存する
     * @param App\Models\SpecialEstate $row
     * @param Library\Custom\Estate\Setting\Special $setting
     */
    public function updateSetting($row, $setting) {
        $row->setFromArray($setting->toSaveData());
        $row->updated_at = date('Y-m-d H:i:s');
        $row->save();
        return $row->id;
    }

    /**
     * コピーファイル名を取得する
     */
    public function getCopyFilename($hpId, $settingId) {
        $name = 'sp-'.date('YmdHis');
        for ($i=0;$i<5;$i++) {
            $filename = $name.sprintf('%02d', $i);
            if ($this->isUniqueFilename($filename, $hpId, $settingId)) {
                return $filename;
            }
        }
        return false;
    }


    /**
     * 本番未反映のデータがあるか
     * @param int $hpId
     * @param int $cmsSettingId
     * @param int $pubSettingId
     */
    public function hasChanged($hpId, $cmsSettingId, $pubSettingId) {

        $select = $this->model->withoutGlobalScopes()->from($this->_name . ' AS cms');
        $select->join($this->_name.' AS pub', function($join) use ($pubSettingId) {
            $join->on('cms.origin_id', 'pub.origin_id')
                ->where('pub.hp_estate_setting_id', $pubSettingId)
                ->on('cms.hp_id', 'pub.hp_id')
                ->where('pub.delete_flg', 0);
        });

        $select->where('cms.hp_id', $hpId);
        $select->where('cms.hp_estate_setting_id', $cmsSettingId);
        $select->where('cms.delete_flg', 0);
        $select->whereRaw('cms.update_date > pub.update_date');

        $row = $select->first();

        return !!$row;
    }

    /**
     * データコピー
     * @param App\Models\HpEstateSetting $cmsSettingRow
     * @param App\Models\HpEstateSetting $toSettingRow
     * @param array $targetIds
     */
    public function copyTo($cmsSettingRow, $toSettingRow, $targetIds, $reserveList) {

        // 公開中の特集データを取得
        $fromRowset = [];
        if (count($targetIds) > 0) {
            $where      = [
                ['hp_id', $cmsSettingRow->hp_id],
                ['hp_estate_setting_id', $cmsSettingRow->id],
                'whereIn' => ['id', $targetIds],
            ];
            $fromRowset = $this->fetchAll($where);
        }

        // origin_idでコピー先レコードを検索
        $targetOriginIds = [];
        foreach ($fromRowset as $row) {
            $targetOriginIds[] = $row->origin_id;
        }

        // 既存の公開中レコードから最新の公開中以外のデータを削除
        $where = [
            ['hp_id', $toSettingRow->hp_id],
            ['hp_estate_setting_id', $toSettingRow->id],
        ];
        if (count($targetOriginIds) > 0) {
            $where['whereNotIn'] = ['origin_id', $targetOriginIds];
        }
        $this->delete($where, true);

        if (count($targetOriginIds) < 1) {
            return;
        }

        $toRowset   = $this->fetchAll([['hp_id', $toSettingRow->hp_id], ['hp_estate_setting_id', $toSettingRow->id], 'whereIn' => ['origin_id', $targetOriginIds]]);

        $toSpecials = [];
        foreach ($toRowset as $row) {
            $toSpecials[$row->origin_id] = $row;
        }

        $toPublics = [];
        if (count($reserveList) > 0) {
            $toSettingPublic = \App::make(HpEstateSettingRepositoryInterface::class)->getSetting($toSettingRow->hp_id, config('constants.hp_estate_setting.SETTING_FOR_PUBLIC'))->id;
            $toRowsetPublic   = $this->fetchAll([['hp_id', $toSettingRow->hp_id], ['hp_estate_setting_id', $toSettingPublic], 'whereIn' => ['origin_id', $reserveList]]);
            foreach ($toRowsetPublic as $row) {
                $toPublics[$row->origin_id] = $row;
            }
        }

        // コピー処理
        foreach ($fromRowset as $row) {
            if (!in_array($row->id, Special_Make_Rowset::getInstance()->filterPublicIdsUpdateNow()) && !in_array($row->id, $reserveList)) {
                continue;
            }
            if (count($toPublics) > 0 && in_array($row->id, $reserveList)) {
                $data = $toPublics[$row->id]->toArray();
            } else {
                $data = $row->toArray();
            }
            unset($data['id']);
            unset($data['hp_estate_setting_id']);
            unset($data['update_date']);
            unset($data['create_date']);

            // コピー先の設定IDをセット
            $data['hp_estate_setting_id'] = $toSettingRow->id;
            $data['update_date'] = date('Y-m-d H:i:s');

            // 既存レコードがある場合は更新
            if (isset($toSpecials[$row->origin_id])) {
                $newRow = $toSpecials[$row->origin_id];
                $newRow->setFromArray($data);
                $newRow->save();
            }
            else {
                $newRow = $this->create($data);
            }
        }
    }

    /**
     * 公開/非公開にした日時を保存
     *
     * @param array $updatedSpecialIds
     */
    public function updatePublishedAt(array $updatedSpecialIds) {

        if (count($updatedSpecialIds) < 1) {
            return;
        }

        $where = [
            'whereIn' => ['id', $updatedSpecialIds],
        ];
        $this->update($where, ['published_at' => date('Y-m-d H:i:s')]);
    }


    /**
     * @param $hpId
     * @param $settingId
     * @param $orderOption
     */
    public function fetchAllIdsWithPubStatus($hpId, $settingId, array $ids = array(), $orderOption = null,  $count = null, $offset = null ) {
        $select = $this->_createSelectWithPubStatus(true, $hpId, $settingId, $orderOption, $count, $offset);
        if(is_array($ids) && !empty($ids)){
            $select->where('cms.origin_id IN (?)', $ids);
        }
        $this->setAutoLogicalDelete(false);
        $rowset = $this->fetchAll($select);
        $this->setAutoLogicalDelete(true);
        $rowset->setFoundRows((int) $this->getFoundRow($select));

        return $rowset;
    }

    public function toOrderByFromSortOption($option, $prefix = '') {
        $orderby = '';
        switch ($option) {
            case config('constants.special_estate.ORDER_CREATED_ASC'):
                $orderby = 'create_special_date asc';
                break;
            case config('constants.special_estate.ORDER_TITLE_ASC'):
                $orderby = 'title asc';
                break;
            case config('constants.special_estate.ORDER_TITLE_DESC'):
                $orderby = 'title desc';
                break;
            case config('constants.special_estate.ORDER_PUB_STATUS'):
                $orderby = 'pub_status desc';
                break;
            case config('constants.special_estate.ORDER_ESTATE_CLASS'):
                $orderby = 'estate_class asc';
                break;
            case config('constants.special_estate.ORDER_CREATED_DESC_ID_DESC'):
                $orderby =  array('create_special_date desc','id desc');
                break;
            case config('constants.special_estate.ORDER_CREATED_DESC'):
            default:
                $orderby = 'create_special_date desc';
                break;
        }
        $orderArray = array();
        if($prefix){
            $prefix .= '.';
        }
        if(is_array($orderby) && !empty($orderby)){
            foreach($orderby as $v){
                $orderArray[] = $prefix.$v;
            }
        }
        else  {
            $orderArray[] = $prefix.$orderby;
        }

        return $orderArray;
    }

    static public function getSortOptions() {
		return [
			config('constants.special_estate.ORDER_CREATED_DESC') => '作成日が新しい順',
			config('constants.special_estate.ORDER_CREATED_ASC')  => '作成日が古い順',
			config('constants.special_estate.ORDER_TITLE_ASC')   => '特集名の昇順',
			config('constants.special_estate.ORDER_TITLE_DESC')   => '特集名の降順',
			config('constants.special_estate.ORDER_PUB_STATUS')   => '状態',
			config('constants.special_estate.ORDER_ESTATE_CLASS') => '物件種別',
		];
	}
}