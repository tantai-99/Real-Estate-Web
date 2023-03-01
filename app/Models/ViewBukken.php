<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class ViewBukken extends Model {
    use MySoftDeletes;

    protected $table = 'view_bukken';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'user_id',
        'bukken_no',
        'delete_flg',
        'create_date',
        'update_date',
    ];
}