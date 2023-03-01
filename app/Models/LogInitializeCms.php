<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class LogInitializeCms extends Model
{
    use MySoftDeletes;

    protected $table = 'log_initialize_cms';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'manager_id',
        'hp_id',
        'company_id',
        'datetime',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}