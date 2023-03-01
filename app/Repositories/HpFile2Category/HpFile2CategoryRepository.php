<?php
namespace App\Repositories\HpFile2Category;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpFile2CategoryRepository extends BaseRepository implements HpFile2CategoryRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpFile2Category::class;
    }

}
