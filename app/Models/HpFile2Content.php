<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpFile2Content extends Model {
    use MySoftDeletes;

    protected $table = 'hp_file2_content';
    public $timestamps = false;
    protected $primaryKey = 'aid';
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'aid',
        'id',
        'filename',
        'extension',
        'content',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
    public function hpFile2ContentLength()
    {
        return $this->hasOne(HpFile2ContentLength::class, 'hp_file2_content_id');
    }
}