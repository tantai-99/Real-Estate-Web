<?php
namespace App\Repositories\HpTopImage;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
class HpTopImageRepository extends BaseRepository implements HpTopImageRepositoryInterface {

	protected $_name = 'hp_top_image';

    public function getModel()
    {
        return \App\Models\HpTopImage::class;
    }
}