<?php

namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Traits\MySoftDeletes;

class MLayout extends Model
{
    use MySoftDeletes;

    protected $table = 'm_layout';   
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
    'id',
    'name',
    'theme_name',
    'delete_flg',
    'create_id',
    'create_date',
    'update_id',
    'update_date',
    ];

    public function hp() {
        return $this->hasOne(Hp::class, 'layout_id');
    }
}
