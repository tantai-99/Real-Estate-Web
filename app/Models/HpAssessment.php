<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Illuminate\Support\Facades\App;

class HpAssessment extends Model
{
    use MySoftDeletes;

    protected $table = 'hp_assessment';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
    
    protected $fillable = [
        'id',
        'hp_id',
        'date',
        'point',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hp(){
        return $this->belongsTo(Hp::class, 'hp_id','id');
    }
}