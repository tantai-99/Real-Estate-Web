<?php

namespace App\Repositories\HpArea;

use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class HpAreaRepository extends BaseRepository implements HpAreaRepositoryInterface
{
    public function getModel()
    {
        return \App\Models\HpArea::class;
    }

	public function save($pageRow, $sort, $columnName, $areaTypeCode) {
        $data = array(
            'area_type_code'   => $areaTypeCode,
            'column_type_code' => (int)str_replace('column', '', $columnName),
            'sort'             => $sort,
            'page_id'          => $pageRow->id,
            'hp_id'            => $pageRow->hp_id,
        );

        return $this->model->create($data);
    }

    static protected $_columnTypes = array(
            1 => '1列',
            2 => '2列',
            3 => '3列',
    );

    static public function getColumnTypes() {
        return self::$_columnTypes;
    }
}
