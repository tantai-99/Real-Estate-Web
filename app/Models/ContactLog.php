<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class ContactLog extends Model
{
    use MySoftDeletes;

    protected $table = 'contact_log';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $fillable = [
        'id',
        'page_type_code',
        'notification_to_1',
        'notification_to_2',
        'notification_to_3',
        'notification_to_4',
        'notification_to_5',
        'notification_subject',
        'body',
        'hp_id',
        'company_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}