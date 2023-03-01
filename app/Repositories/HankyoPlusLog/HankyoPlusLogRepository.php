<?php
namespace App\Repositories\HankyoPlusLog;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use function Symfony\Component\Translation\t;

class HankyoPlusLogRepository extends BaseRepository implements HankyoPlusLogRepositoryInterface
{
    protected $_name = 'hankyo_plus_log';

    public function getModel()
    {
        return \App\Models\HankyoPlusLog::class;
    }

    public function saveOperation($operation, $hpId, $companyId) {
        $data = array(
            'operation'  => $operation,
            'hp_id'      => $hpId,
            'company_id' => $companyId,
       );
        return $this->create($data);
    }
}