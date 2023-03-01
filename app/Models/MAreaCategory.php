<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class MAreaCategory extends Model
{
    use MySoftDeletes;

    protected $table = 'm_area_category';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'area_name',
        'delete_flg'
    ];
}
