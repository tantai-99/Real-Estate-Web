<?php 
namespace App\Repositories\HpInfoDetailLink;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
class HpInfoDetailLinkRepository extends BaseRepository implements HpInfoDetailLinkRepositoryInterface
{
	
    protected $_name = 'hp_info_detail_link';
    
    public function getModel()
    {
        return \App\Models\HpInfoDetailLink::class;
    }

    public function getData($pageId, $hpId) {
        $select = $this->model->select();
        $select->where("page_id", $pageId);
        $select->where("hp_id", $hpId);
        return $select->first();
    }

    public function getDataByHp( $hpId) {
        $select = $this->model->select();
        $select->where("hp_id", $hpId);
        return $select->get();
    }
	
}