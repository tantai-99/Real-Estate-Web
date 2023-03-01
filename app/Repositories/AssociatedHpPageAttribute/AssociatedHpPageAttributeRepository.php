<?php

namespace App\Repositories\AssociatedHpPageAttribute;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class AssociatedHpPageAttributeRepository extends BaseRepository implements AssociatedHpPageAttributeRepositoryInterface
{
    protected $_name = 'associated_hp_page_attribute';

    public function getModel()
    {
        return \App\Models\AssociatedHpPageAttribute::class;
    }

    public function save($pageId, $hpMainPartsId, $hpId) {
        $data = array(
            'hp_page_id'        => $pageId,
            'hp_main_parts_id'  => $hpMainPartsId,
            'hp_id'             => $hpId,
        );
        return $this->create($data);
    }

    public function fetchRowById($pageId) {
        $select = [['hp_page_id', $pageId]];
        return $this->fetchRow($select);
    }
    
    public function getMainPartById($pageId)
    {
        $select = $this->model->select('hp_main_parts_id');
        $select->where('hp_page_id', $pageId);
        return $select->first();
    }

    public function fetchRowByHpId($linkId,$hpId){
        $select = [
            ['hp_id', $hpId],
            ['hp_page_id', $linkId]
        ];
        return $this->fetchRow($select);
    }
}