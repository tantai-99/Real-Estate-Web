<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpFileContentLength extends Model {
    use MySoftDeletes;

    protected $table = 'hp_file_content_length';
    protected $primaryKey = 'aid';
    public $timestamps = false;

    protected $fillable = [
        'aid',
        'hp_file_content_id',
        'content_length',
        'create_date',
        'update_date',
    ];
}