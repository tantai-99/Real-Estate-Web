<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class Conversion extends Model
{
    use MySoftDeletes;

    protected $table = 'conversion';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $fillable = [
        'id',
        'conversion_type',
        'page_url',
        'device',
        'user_ip',
        'user_agent',
        'company_id',
        'recieve_date',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}