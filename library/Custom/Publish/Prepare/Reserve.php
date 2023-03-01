<?php
namespace Library\Custom\Publish\Prepare;

use App;
use App\Repositories\Hp\HpRepositoryInterface;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Repositories\ReleaseSchedule\ReleaseScheduleRepositoryInterface;
use App\Repositories\ReleaseScheduleSpecial\ReleaseScheduleSpecialRepositoryInterface;

class Reserve extends PrepareAbstract {

    protected $hpRepository;
    protected $companyRepository;
    protected $reserveRepository;
    protected $reserveSpecialRepository;

    public function __construct($hpId) {
        $this->hpRepository = App::make(HpRepositoryInterface::class);
        $this->companyRepository = App::make(CompanyRepositoryInterface::class);
        $this->reserveRepository = App::make(ReleaseScheduleRepositoryInterface::class);
        $this->reserveSpecialRepository = App::make(ReleaseScheduleSpecialRepositoryInterface::class);
        $this->setHpRow($this->hpRepository->find($hpId));
        $this->setCompanyRow($this->companyRepository->fetchRowByHpId($hpId));
        // $this->setNamespace(new Zend_Session_Namespace('publish'));
    }

    /**
     * 予約更新
     */
    public function updateReserve($params = null) {

        if (!$params) {
            $params = $this->getNamespace('publish')->params;
        }

        // 初期化
        $this->reserveRepository->deletePrereserved($this->getHpRow()->id);
        $this->reserveRepository->deleteReserved($this->getUpdatePageIds($params));

        if(isset($params['page'])) {
            foreach ($params['page'] as $id => $val) {
                if ($val['update'] && $val['new_release_at']) {
                    $this->reserveRepository->save(config('constants.release_schedule.RESERVE_RELEASE'), $this->getHpRow()->id, $id, $this->dateForDb($val['new_release_at']), $this->getCompanyRow()->id);
                }
                if ($val['update'] && $val['new_close_at']) {
                    $this->reserveRepository->save(config('constants.release_schedule.RESERVE_CLOSE'), $this->getHpRow()->id, $id, $this->dateForDb($val['new_close_at']), $this->getCompanyRow()->id);
                }
            }
        }
//         $adapter->commit();
    }

    /**
     * 予約のパブリッシュ完了処理
     */
    public function completeReserve() {


    }

    /**
     * パブリッシュ設定後も有効な予約を取得
     *
     * @param $updatePageIds
     * @return array
     */
    public function survivingReserve($updatePageIds) {

        $res = array();
        foreach ($this->reserveRepository->fetchReserveRowsByHpId($this->getHpRow()->id) as $row) {
            if (!in_array($row->page_id, $updatePageIds)) {
                $res[] = $row;
            }
        }
        return $res;
    }

    /**
     * 新たに設定された予約と既存の予約をマージ
     *
     * @param $params
     * @param $pageIds
     * @return array
     */
    public function mergeReserve($params, $pageIds) {

        $res = [];

        if (isset($params['page'])) {

            foreach ($params['page'] as $param) {

                if (isset($param['new_release_at']) && $param['new_release_at']) {
                    $res[] = $this->dateForDb($param['new_release_at']);
                }

                if (isset($param['new_close_at']) && $param['new_close_at']) {
                    $res[] = $this->dateForDb($param['new_close_at']);
                }
            }
        }

        foreach ($this->survivingReserve($pageIds) as $row) {
            $res[] = $row->release_at;
        }
        asort($res);
        return array_unique($res);
    }

    /**
     * 仮予約を保存
     */
    public function savePrereserved() {

        $params = $this->getNamespace('publish')->params;
        $table = $this->reserveRepository;

//         $adapter = $table->getAdapter();
//         $adapter->beginTransaction();

        $table->deletePrereserved($this->getHpRow()->id);

        foreach ($this->getUpdatePageIds($params) as $pageId) {

            $p = $params['page'][$pageId];
            if ($p['update'] && $p['new_release_flg']) {
                $table->save(config('constants.release_schedule.RESERVE_RELEASE_PRE'), $this->getHpRow()->id, $pageId, $p['new_release_at'] ? $this->dateForDb($p['new_release_at']) : null, $this->getCompanyRow()->id);
            }
            if ($p['update'] && $p['new_close_flg']) {
                $table->save(config('constants.release_schedule.RESERVE_CLOSE_PRE'), $this->getHpRow()->id, $pageId, $p['new_close_at'] ? $this->dateForDb($p['new_close_at']) : null, $this->getCompanyRow()->id);
            }
        }
//         $adapter->commit();
    }

    /**
     * 仮予約を取得
     * @return array
     */
    public function getPrereserved() {

        $res = array();

        foreach ($this->reserveRepository->fetchPrereservedRows($this->getHpRow()->id) as $row) {
            $res['page_'.$row->page_id.'_update'] = $true = 1;

            if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE_PRE')) {
                $res['page_'.$row->page_id.'_new_release_flg'] = $true;
            }
            if ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE_PRE')) {
                $res['page_'.$row->page_id.'_new_close_flg'] = $true;
            }
            if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE_PRE') && $row->release_at) {
                $res['page_'.$row->page_id.'_new_release_at'] = $this->dateForApp($row->release_at);
            }
            if ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE_PRE') && $row->release_at) {
                $res['page_'.$row->page_id.'_new_close_at'] = $this->dateForApp($row->release_at);
            }
        }
        return $res;
    }

    public function getReserved() {

        $res = array();

        foreach ($this->reserveRepository->fetchReserveRowsByHpId($this->getHpRow()->id) as $row) {
            $res['page_'.$row->page_id.'_update'] = $true = 1;

            if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE')) {
                $res['page_'.$row->page_id.'_new_release_flg'] = $true;
            }
            if ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE')) {
                $res['page_'.$row->page_id.'_new_close_flg'] = $true;
            }
            if ($row->release_type_code == config('constants.release_schedule.RESERVE_RELEASE') && $row->release_at) {
                $res['page_'.$row->page_id.'_new_release_at'] = $this->dateForApp($row->release_at);
            }
            if ($row->release_type_code == config('constants.release_schedule.RESERVE_CLOSE') && $row->release_at) {
                $res['page_'.$row->page_id.'_new_close_at'] = $this->dateForApp($row->release_at);
            }
        }
        return $res;
    }


    // @todo delete later
    //    /**
    //     * @return bool
    //     */
    //    public function hasPrereserved() {
    //
    //        return $this->reserveRepository->countPrereserved($this->getHpRow()->id) > 0;
    //
    //    }

}

?>
