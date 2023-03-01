<?php
namespace App\Repositories\Hp;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\Translation\t;

class HpRepository extends BaseRepository implements HpRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\Hp::class;
    }

}
