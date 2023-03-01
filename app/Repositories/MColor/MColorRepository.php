<?php
namespace App\Repositories\MColor;

use App\Repositories\BaseRepository;
use App\Models\MColor;

use function Symfony\Component\Translation\t;

class MColorRepository extends BaseRepository implements MColorRepositoryInterface
{
    protected $_name = 'm_color';

    public function getModel()
    {
        return \App\Models\MColor::class;
    }

}