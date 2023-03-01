<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HpFile2Used extends Model
{
    use HasFactory;

    protected $table = 'hp_file2_used';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'hp_page_id',
        'hp_file2_id',
        'hp_id',
        'delete_flg', 
        'create_id', 
        'create_date', 
        'update_id', 
        'update_date',
    ];
    public function hpFile2() {
        return $this->belongsTo(HpFile2::class, 'hp_file2_id');
    }
}