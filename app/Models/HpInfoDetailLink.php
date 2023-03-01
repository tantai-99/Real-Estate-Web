<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpInfoDetailLink extends Model {
    use MySoftDeletes;

    protected $table = 'hp_info_detail_link';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
    	'id', 
        'link_type',
        'link_url',
        'link_page_id',
        'file2', 
        'file2_title', 
        'link_target_blank', 
        'page_id', 
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
        'link_house',
    ];
}
