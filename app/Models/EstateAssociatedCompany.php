<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class EstateAssociatedCompany extends Model
{
    use MySoftDeletes;

    protected $table = 'estate_associated_company';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'parent_company_id',
        'subsidiary_member_no',  
        'delete_flg',    
        'create_id', 
        'create_date',   
        'update_id', 
        'update_date',
    ];
}