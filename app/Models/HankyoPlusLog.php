<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class HankyoPlusLog extends Model
{
    use MySoftDeletes;

    protected $table = 'hankyo_plus_log';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $fillable = [
        'id',
        'operation',
        'hp_id',
        'company_id',
        'delete_flg',
        'create_date',
        'update_date',
    ];
}