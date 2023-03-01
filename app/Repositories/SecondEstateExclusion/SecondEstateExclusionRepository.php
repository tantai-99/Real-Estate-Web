<?php
namespace App\Repositories\SecondEstateExclusion;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class SecondEstateExclusionRepository extends BaseRepository implements SecondEstateExclusionRepositoryInterface
{
    use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\SecondEstateExclusion::class;
    }
    public function getDataForId($id) {

        $select = $this->model->select();
        $select->where('id', $id);
        return $this->fetchRow($select);
    }

    /**
     * 加盟店IDで取得
     */
    public function getDataForCompanyId($company_id) {

        $select = $this->model->select();
        $select->where('company_id', $company_id);
        return $select->get();
    }

    /**
     * 加盟店IDとIDで取得
     */
    public function getDataForMemberNoCompanyId($member_no, $company_id, $hp_id) {
        $select = $this->model->select();
        $select->where('member_no', $member_no);
        $select->where('company_id', $company_id);
        $select->where('hp_id', $hp_id);
        return $select->first();
    }

    public function getExcluded($count = null, $offset = null) {
        $select = $this->model->withoutGlobalScopes()->selectRaw('seExclusion.*');
        $select->from('second_estate_exclusion AS seExclusion');
        $select->where('seExclusion.delete_flg', 0);
        $select->groupBy('seExclusion.member_no');
        $select->orderBy('seExclusion.id');
        if ($count !== null || $offset !== null) {
            $select->skip($offset)->take($count);
        }

        return $select->get();
    }
}