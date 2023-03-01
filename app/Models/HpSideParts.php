<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class HpSideParts extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_side_parts';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    protected $fillable = [
        'id',
        'parts_type_code',
        'sort',
        'page_id',
        'hp_id',
        'copied_id',
        'display_flg',
        'attr_1',
        'attr_2',
        'attr_3',
        'attr_4',
        'attr_5',
        'attr_6',
        'attr_7',
        'attr_8',
        'attr_9',
        'attr_10',
        'attr_11',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}