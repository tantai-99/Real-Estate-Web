<?php
namespace App\Repositories\MLayout;

use App\Repositories\BaseRepository;

use function Symfony\Component\Translation\t;

class MLayoutRepository extends BaseRepository implements MLayoutRepositoryInterface
{
    protected $_name = 'm_layout';

    public function getModel()
    {
        return \App\Models\MLayout::class;
    }

}