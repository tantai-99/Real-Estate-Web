<?php
namespace App\Repositories\SecondEstate;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class SecondEstateRepository extends BaseRepository implements SecondEstateRepositoryInterface
{
    use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\SecondEstate::class;
    }

    public function getTagDataForId($id) {

        $select = $this->model->select();
        $select->where('id', $id);
        $row = $this->fetchRow($select);
        return $row;
    }

    /**
     * 加盟店IDで取得
     */
    public function getDataForCompanyId($company_id) {

        $select = $this->model->select();
        $select->where('company_id', $company_id);
        return $select->first();

    }
}
