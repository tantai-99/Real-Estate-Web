<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpMainElement extends Model {
    use MySoftDeletes;

    protected $table = 'hp_main_element';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';


    protected $fillable = [
        'id',
        'type',
        'sort',
        'parts_id',
        'page_id',
        'hp_id',
        'copied_id',
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
        'attr_12',
        'attr_13',
        'attr_14',
        'attr_15',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}