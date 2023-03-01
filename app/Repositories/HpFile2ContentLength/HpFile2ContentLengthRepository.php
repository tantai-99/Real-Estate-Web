<?php

namespace App\Repositories\HpFile2ContentLength;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class HpFile2ContentLengthRepository extends BaseRepository implements HpFile2ContentLengthRepositoryInterface
{   
    public function getModel()
    {
        return \App\Models\HpFile2ContentLength::class;
    }
}