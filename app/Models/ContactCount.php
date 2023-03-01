<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class ContactCount extends Model
{
    use MySoftDeletes;

    protected $table = 'contact_count';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $fillable = [
        'id',
        'page_type_code',
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
        'fontend_send_date',
        'gmo_send_date',

    ];

    public function company()
    {
        return $this->belongsToMany(Company::class,'company_id');
    }
}