<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpImageContent extends Model {
    use MySoftDeletes;

    protected $table = 'hp_image_content';
    public $timestamps = false;
    protected $primaryKey = 'aid';
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'aid',
        'id',
        'extension',
        'content',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}