<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class LogEdit extends Model
{
    use MySoftDeletes;

    protected $table = 'log_edit';   
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
 
    protected $fillable = [
        'id',
        'type',
        'athome_staff_id',
        'page_id',
        'hp_id',
        'company_id',
        'edit_type_code',
        'attr1',
        'datetime',
        'user_ip',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hp($col){
        return $this->belongsTo(Hp::class,$col);
    }

    public function company(){
        return $this->belongsToMany(Company::class,'company_id');
    }
}
