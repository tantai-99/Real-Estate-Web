<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class HpSiteImage extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_site_image';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'type',
        'extension',
        'content',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',

    ];

    public function hp()
    {
        return $this->belongsTo(Hp::class, 'hp_id');
    }
}
