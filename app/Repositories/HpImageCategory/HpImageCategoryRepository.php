<?php
namespace App\Repositories\HpImageCategory;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpImageCategoryRepository extends BaseRepository implements HpImageCategoryRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpImageCategory::class;
    }

}
