<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class AssociatedCompanyFdp extends Model
{
    use MySoftDeletes;

    protected $table = 'associated_company_fdp';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'start_date',
        'end_date',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',

    ];
}
