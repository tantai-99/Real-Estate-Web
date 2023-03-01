<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Traits\MySoftDeletes;

class PublishProgress extends Model
{
    use MySoftDeletes;

    protected $table = 'publish_progress';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [ 
        'id',
        'session_id',
        'process_id',
        'environment',
        'hostname',
        'publish_type',
        'company_id',
        'hp_id',
        'login_id',
        'all_upload_flg',
        'start_time',
        'num_of_pages',
        'finish_time',
        'status',
        'exception_msg',
        'reported_flg',
        'progress',
        'success_notify',
        'create_date',
        'update_date',
    ];

    public function company()
    {
        return $this->belongsToMany(Company::class, 'company_id');
    }
}