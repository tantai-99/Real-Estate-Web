<?php

namespace App\Repositories\HpContact;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpContactRepository extends BaseRepository implements HpContactRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpContact::class;
    }

    public function fetchInfo($hpId) {
        $select = $this->model->select('notification_to_1', 'notification_to_2', 'notification_to_3', 'notification_to_4', 'notification_to_5', 
        'notification_subject', 'autoreply_flg', 'autoreply_from', 'autoreply_sender', 'autoreply_subject', 'autoreply_body', 
        'heading_code', 'heading', 'page_id', 'hp_id', 'api_key');
        $select->where('hp_id', $hpId);

        return $select->first();
    }

}
