<?php

namespace App\Repositories\AssociatedCompanyHp;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Models\AssociatedCompanyHp;
use function Symfony\Component\Translation\t;

class AssociatedCompanyHpRepository extends BaseRepository implements AssociatedCompanyHpRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\AssociatedCompanyHp::class;
    }

    public function fetchRowByCurrentHpId($hpId)
    {
        return $this->model->select()->where('current_hp_id', $hpId)->first();

    }

    public function fetchRowBySpaceHpId($hpId)
    {

        return $this->model->select()->where('space_hp_id', $hpId)->first();
    }

    public function fetchRowByCompanyId($companyId)
    {

        return $this->model->select()->where('company_id', $companyId)->first();
    }

    public function updateCurrentHp($companyId, $hpId)
    {   
        AssociatedCompanyHp::where('company_id',$companyId)->update(['current_hp_id' => $hpId ]);
    }

    public function updateCurrentHpForRollback($companyId, $hpId)
    {
        AssociatedCompanyHp::where('company_id',$companyId)->update(['current_hp_id' => $hpId, 'backup_hp_id' => null ]);
    }

    public function updateCreatorHp($companyId, $hpId)
    {
        AssociatedCompanyHp::where('company_id',$companyId)->update(['space_hp_id' => $hpId ]);
    }

    public function updateBackupHp($companyId, $hpId)
    {
        AssociatedCompanyHp::where('company_id',$companyId)->update(['backup_hp_id' => $hpId ]);
    }   

    public function deleteCreatorHp($companyId)
    {
        // $this->update(array('space_hp_id' => null, 'backup_hp_id' => null), array('company_id' => $companyId));
        $this->update($companyId,['space_hp_id'=>null,'backup_hp_id'=>null]);
    }
}