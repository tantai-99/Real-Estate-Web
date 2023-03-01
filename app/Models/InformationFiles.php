<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class InformationFiles extends Model
{
    use MySoftDeletes;

    protected $table = 'information_files';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'name',
        'contents',
        'extension',
        'information_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date'
    ];

    public function information()
    {
        return $this->belongsTo(Information::class, 'information_id');
    }
}
