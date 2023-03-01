<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class Information extends Model
{
    use MySoftDeletes;

    protected $table = 'information';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'title',
        'start_date',
        'end_date',
        'display_page_code',
        'important_flg',
        'new_flg',
        'display_type_code',
        'url',
        'contents',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date'
    ];

    public function informationFiles()
    {
        return $this->hasMany(Information::class, 'information_id');
    }
}
