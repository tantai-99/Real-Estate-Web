<?php

namespace App\Repositories\ReleaseScheduleSpecial;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Repositories\AssociatedCompanyHp\AssociatedCompanyHpRepositoryInterface;


class ReleaseScheduleSpecialRepository extends BaseRepository implements ReleaseScheduleSpecialRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\ReleaseScheduleSpecial::class;
    }

    private $typeList    = [];
    private $typeListPre = [];

    public function __construct() {
        parent::__construct();
        $this->typeList = [config('constants.release_schedule.RESERVE_RELEASE'), config('constants.release_schedule.RESERVE_CLOSE')];
        $this->typeListPre = [config('constants.release_schedule.RESERVE_RELEASE_PRE'), config('constants.release_schedule.RESERVE_CLOSE_PRE')];
    }

    public function checkHasReserve($hpId) {

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

    public function checkHasPrereserve($hpId) {
        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['release_type_code',
                $this->typeListPre
            ],
            ['completion_flg', 0],
        ];
        return $this->countRows($where, true) > 0;
    }

    /**
     *
     * @param $hpId int
     */
    public function fetchAllReserve($hpId) {

        $where = [
            ['hp_id', $hpId], 
            'whereIn' => ['release_type_code', $this->typeList],
            ['completion_flg', 0]
        ];
        $order = ['ASC' => 'release_at'];
        return $this->fetchAll($where, $order);
    }

    /**
     *
     * @param $hpId int
     */
    public function fetchAllPrereserve($hpId) {

        $where = [['hp_id', $hpId], 'whereIn' => ['release_type_code', $this->typeListPre], ['completion_flg', 0]];
        $order = ['ASC' => 'release_at'];
        return $this->fetchAll($where, $order);
    }

    public function savePrereserve($hp, array $params) {

        // delete old
        $data = ['whereIn' => ['release_type_code', $this->typeListPre], ['hp_id', $hp->id]];
        $this->delete($data, true);

        $company= \App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRowByCurrentHpId($hp->id);

        // saving
        foreach ($params as $id => $param) {

            if (!$param['update']) {
                continue;
            }

            // save
            foreach (['release', 'close'] as $name) {

                // release
                $release_at = null;

                // simple && close
                if (!isset($param["new_{$name}_flg"]) && $name === 'close') {
                    continue;
                }

                // detail && 更新対象外
                if (isset($param["new_{$name}_flg"]) && !$param["new_{$name}_flg"]) {
                    continue;
                }

                $release_at = isset($param["new_{$name}_at"]) && $param["new_{$name}_at"] ? $this->dateForDb($param["new_{$name}_at"]) : null;

                $upper = strtoupper($name);
                $data  = [
                    'release_type_code' => config("constants.release_schedule.RESERVE_{$upper}_PRE"),
                    'hp_id'             => $hp->id,
                    'special_estate_id' => $id,
                    'release_at'        => $release_at,
                    'completion_flg'    => 0,
                    'company_id'        => $company ? $company->company_id : null,
                ];
                $this->create($data);
            }
        }

    }

    public function saveReserve($hp, array $params) {

        // delete pre reserve
        $data = [
            'whereIn' => ['release_type_code',
            $this->typeListPre],
            ['hp_id', $hp->id]
        ];
        $this->delete($data, true);

        $company = \App::make(AssociatedCompanyHpRepositoryInterface::class)->fetchRowByCurrentHpId($hp->id);

        // saving
        foreach ($params as $id => $param) {

            if (!$param['update']) {
                continue;
            }

            // delete old reserve
            $data = [
                ['special_estate_id', $id],
                'whereIn' => ['release_type_code', $this->typeList],
                ['hp_id', $hp->id],
            ];
            $this->delete($data, true);

            $list = ['release', 'close'];

            // save
            foreach ($list as $name) {

                if (isset($param["new_{$name}_at"]) && $param["new_{$name}_at"]) {
                    $upper = strtoupper($name);
                    $data  = [
                        'release_type_code' => config("constants.release_schedule.RESERVE_{$upper}"),
                        'hp_id'             => $hp->id,
                        'special_estate_id' => $id,
                        'release_at'        => $this->dateForDb($param["new_{$name}_at"]),
                        'completion_flg'    => 0,
                        'company_id'        => $company ? $company->company_id : null,
                    ];
                    $this->create($data);
                }
            }
        }
    }

    public function dateForApp($datetime) {
        return Carbon::parse($datetime)->format("Y年m月d日H時");
    }

    public function dateForDb($datetime) {

        $datetime = str_replace(' ', '', $datetime);

        $yyyyy = mb_substr($datetime, 0, 4);
        $mm    = mb_substr($datetime, 5, 2);
        $dd    = mb_substr($datetime, 8, 2);
        $hh    = mb_substr($datetime, 11, 2);

        return "{$yyyyy}-{$mm}-{$dd} {$hh}:00:00";
    }

    public function fetchReseveRowsBeforeNow() {

        $where= [
            ['release_at', date("Y/m/d H:i:s", time())],
            ['completion_flg', 0],
            'whereIn' => ['release_type_code', $this->typeList],
        ];
        return $this->fetchAll($where);
    }

    /**
     * 予約の有無
     *
     * @param $hpId
     * @return bool
     */
    public function hasReserveByHpId($hpId, $specialEstateId = null) {

        $where = [
            ['hp_id', $hpId],
            'whereIn' => ['release_type_code', $this->typeList],
            ['completion_flg', 0],
        ];

        if ($specialEstateId != null) {
            $where[] = ['special_estate_id', $specialEstateId];
        };

        if ($this->countRows($where) > 0) {
            return true;
        };

        return false;
    }
}