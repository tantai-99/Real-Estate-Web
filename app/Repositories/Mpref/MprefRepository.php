<?php
namespace App\Repositories\Mpref;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyAccount;
use App\Traits\MySoftDeletes;

use function Symfony\Component\Translation\t;

class MprefRepository extends BaseRepository implements MprefRepositoryInterface
{
    use MySoftDeletes;
    public function getModel()
    {
        return \App\Models\Mpref::class;
    }
}
