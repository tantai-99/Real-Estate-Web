<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpContactParts extends Model {
    use MySoftDeletes;

    protected $table = 'hp_contact_parts';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';


    protected $fillable = [
        'id',
        'item_code',
        'item_title',
        'required_type',
        'choices_type_code',
        'choice_1',
        'choice_2',
        'choice_3',
        'choice_4',
        'choice_5',
        'choice_6',
        'choice_7',
        'choice_8',
        'choice_9',
        'choice_10',
        'choice_11',
        'sort',
        'detail_flg',
        'page_id',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}