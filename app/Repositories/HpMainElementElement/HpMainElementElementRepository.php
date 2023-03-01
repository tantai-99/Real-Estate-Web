<?php

namespace App\Repositories\HpMainElementElement;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpMainElementElementRepository extends BaseRepository implements HpMainElementElementRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpMainElementElement::class;
    }
}
