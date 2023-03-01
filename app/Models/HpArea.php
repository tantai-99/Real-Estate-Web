<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpArea extends Model {
    use MySoftDeletes;

    protected $table = 'hp_area';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';


    protected $fillable = [
        'id',
        'area_type_code',
        'column_type_code',
        'sort',
        'display_flg',
        'page_id',
        'hp_id',
        'copied_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}