<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Casts\AsSubString;

class OriginalSetting extends Model
{
    use MySoftDeletes;

    protected $table = 'original_setting';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

 
    protected $fillable = [
        'id ',
        'company_id',
        'applied_start_date',
        'start_date',
        'contract_staff_id',
        'contract_staff_name',
        'contract_staff_department',
        'applied_end_date',
        'end_date',
        'cancel_staff_id',
        'cancel_staff_name',
        'cancel_staff_department',
        'remarks',
        'delete_flg',
        'create_id  ',
        'create_date',
        'update_id',
        'update_date',
        'global_navigation',
        'all_update_top',
    ];

    protected $casts = [
        'contract_staff_id' => AsSubString::class.':8',
        'cancel_staff_id' => AsSubString::class.':8',
    ];
}
