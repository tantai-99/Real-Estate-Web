<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class HpTopImage extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_top_image';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    protected $fillable = [
        'id',
        'image',
        'image_title',
        'link_type',
        'link_url',
        'link_page_id',
        'file2',
        'file2_title',
        'link_target_blank',
        'sort',
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