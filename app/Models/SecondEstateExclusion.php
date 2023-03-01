<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Casts\AsSubString;

class SecondEstateExclusion extends Model
{
    use MySoftDeletes;

    protected $table = 'second_estate_exclusion';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'hp_id',
        'name',
        'name_kana',
        'address',
        'nearest_station',
        'tel',
        'member_no',
        'delete_by_riyostop_flg',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
    protected $casts = [
        'tel' => AsSubString::class.':11',
    ];
}