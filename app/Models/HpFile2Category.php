<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpFile2Category extends Model {
    use MySoftDeletes;

    protected $table = 'hp_file2_category';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $primaryKey = 'aid';

    protected $fillable = [
        'aid',
        'id',
        'name',
        'sort',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}