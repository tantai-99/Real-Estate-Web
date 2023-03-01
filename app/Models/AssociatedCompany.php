<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class AssociatedCompany extends Model
{
    use MySoftDeletes;

    protected $table = 'associated_company';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'parent_company_id',
        'subsidiary_company_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function company() {
        return $this->belongsToMany(Company::class,"subsidiary_company_id",'id');
    }
}
