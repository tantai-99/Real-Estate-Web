<?php
namespace App\Repositories\LogDelete;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\LogDelete;

use function Symfony\Component\Translation\t;

class LogDeleteRepository extends BaseRepository implements LogDeleteRepositoryInterface {
    
	public function getModel()
    {
        return \App\Models\LogDelete::class;
    }
    
    public function getLastDeleteForComapnyId($company_id) {

        $select = $this->model->select();
        $select->where("company_id", $company_id);
        $select->orderBy("update_date", "DESC");
        return $select->first();
    }
}