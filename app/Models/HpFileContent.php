<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpFileContent extends Model {
    use MySoftDeletes;

    protected $table = 'hp_file_content';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    protected $primaryKey = 'aid';

    protected $fillable = [
        'aid',
        'id',
        'extension',
        'filename',
        'content',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}