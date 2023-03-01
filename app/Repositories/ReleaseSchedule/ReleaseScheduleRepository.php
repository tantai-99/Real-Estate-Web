<?php

namespace App\Repositories\ReleaseSchedule;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;

use function Symfony\Component\Translation\t;

class ReleaseScheduleRepository extends BaseRepository implements ReleaseScheduleRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\ReleaseSchedule::class;
    }

    private $typeList    = [];
    private $typeListPre = [];

    public function __construct() {
        parent::__construct();
        $this->typeList = [config('constants.release_schedule.RESERVE_RELEASE'), config('constants.release_schedule.RESERVE_CLOSE')];
        $this->typeListPre = [config('constants.release_schedule.RESERVE_RELEASE_PRE'), config('constants.release_schedule.RESERVE_CLOSE_PRE')];
    }

    public function save($release_type_code, $hpId, $pageId, $releaseAt, $companyId = null) {

        if (!$companyId) {
            $companyId = App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRowByCurrentHpId($hpId)->company_id;
        }

        $data = [
            'release_type_code' => $release_type_code,
            'hp_id'             => $hpId,
            'page_id'           => $pageId,
            'release_at'        => $releaseAt,
            'completion_flg'    => 0,
            'company_id'        => $companyId,
        ];

        return $this->insertRow($data);
    }

    /**
     * 現在時刻以前の予約で未実行のものをすべて取得
     */
    public function fetchReseveRowsBeforeNow() {

        $where = [
            ['release_at', '<=', date("Y/m/d H:i:s", time())],
            ['completion_flg', 0],
            'whereIn' => ['release_type_code', $this->typeList],
        ];
        return $this->fetchAll($where);
    }

    /**
     * ホームページの予約を取得
     *
     */
    public function fetchReserveRowsByHpId($hpId) {

        $select = [
            ['hp_id', $hpId],
            'whereIn' => ['release_type_code', $this->typeList],
            ['completion_flg', 0],
        ];
        return $this->fetchAll($select);
    }

    /**
     * 予約の有無
     *
     * @param $hpId
     * @return bool
     */
    public function hasReserveByHpId($hpId) {

        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['release_type_code', $this->typeList],
            ['completion_flg', 0],
        ];

        if ($this->countRows($where) > 0) {
            return true;
        };

        return false;
    }

    /**
     * ページの予約情報を取得
     *
     * @param $scheduleRows
     * @param $pageId
     *
     * @return array
     */
    public function fetchReserveByPageId($pageId) {

        $select = [
            'whereIn' => ['release_type_code', $this->typeList],
            ['completion_flg', 0],
            ['page_id', $pageId],
        ];
        return $this->fetchAll($select);
    }

    /**
     * 予約の有無
     *
     * @param $pageIds
     * @return bool
     */
    public function hasReserveByPageIds($pageIds) {

        $select = $this->model()->select();
        $select->whereIn('release_type_code', $this->typeList);
        $select->where('completion_flg', 0);
        $select->whereIn('page_id', $pageIds);

        if ($this->countRows($select) > 0) {
            return true;
        };

        return false;
    }

    /**
     * 仮予約をカウント
     * @return int
     */
    public function countPrereserved($hpId) {

        return $this->fetchPrereservedRows($hpId)->count();
    }

    public function checkHasPreserve($hpId) {

        return $this->countPrereserved($hpId) > 0;
    }

    /**
     * 仮予約を取得
     */
    public function fetchPrereservedRows($hpId) {

        $data = [
            'whereIn' => ['release_type_code', $this->typeListPre],
            ['hp_id', $hpId],
        ];
        return $this->fetchAll($data);
    }

    /**
     * 仮予約の削除
     */
    public function deletePrereserved($hpId) {

        $data = [
            'whereIn' => ['release_type_code', $this->typeListPre],
            ['hp_id', $hpId],
        ];
        $this->delete($data, true);
    }

    /**
     * 予約の複数削除
     *
     * @param $pageIds
     */
    public function deleteReserved($pageIds) {

        $this->delete(['whereIn' => ['page_id', $pageIds]]);
    }

    /**
     * レコードを作成
     *
     * @param $data
     *
     * @return App\Models\Model
     */
    private function insertRow($data) {

        $newRow = $this->create($data);

        $newRow->save();
        return $newRow;
    }
}