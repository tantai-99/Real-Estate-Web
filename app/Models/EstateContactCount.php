<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class EstateContactCount extends Model
{
    use MySoftDeletes;

    protected $table = 'estate_contact_count';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $fillable = [
        'id',
        'page_type_code',
        'estate_number',
        'second_estate_flg',
        'special_id',
        'recommend_flg',
        'from_searchmap',
        'device',
        'user_ip',
        'user_agent',
        'company_id',
        'peripheral_flg',
        'recieve_date',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',

    ];

    public function company()
    {
        return $this->belongsToMany(Company::class,'company_id');
    }
}