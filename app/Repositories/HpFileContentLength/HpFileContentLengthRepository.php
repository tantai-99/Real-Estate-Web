<?php

namespace App\Repositories\HpFileContentLength;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpFileContentLengthRepository extends BaseRepository implements HpFileContentLengthRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpFileContentLength::class;
    }
}