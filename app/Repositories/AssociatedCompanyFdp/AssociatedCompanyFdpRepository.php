<?php
namespace App\Repositories\AssociatedCompanyFdp;
use App\Repositories\BaseRepository;

use Illuminate\Http\Request;
use function Symfony\Component\Translation\t;
use Illuminate\Support\Facades\App;

class AssociatedCompanyFdpRepository extends BaseRepository implements AssociatedCompanyFdpRepositoryInterface
{
    protected $_name = 'associated_company_fdp';


    public function getModel()
    {
        return \App\Models\AssociatedCompanyFdp::class;
    }

    public function save($companyId, $startDate, $endDate) {
        $data = array(
            'company_id'   => $companyId,
            'start_date'   => $startDate,
            'end_date'     => $endDate,
        );
        return $this->create($data);
    }

    public function fetchRowByCompanyId($companyId) {
        if (!$companyId) return;
        $select = $this->model->select();
        $select->where('company_id', $companyId);
        return empty($select->get()->toArray()) ? $select->get()->toArray(): $select->get()->toArray()[0];
    }
}