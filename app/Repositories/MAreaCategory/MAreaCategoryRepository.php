<?php
namespace App\Repositories\MAreaCategory;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyAccount;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class MAreaCategoryRepository extends BaseRepository implements MAreaCategoryRepositoryInterface
{
    use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\MAreaCategory::class;
    }
}
