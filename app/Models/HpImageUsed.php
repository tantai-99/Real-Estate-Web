<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HpImageUsed extends Model
{
    use HasFactory;

    protected $table = 'hp_image_used';
    public $timestamps = false;

    protected $fillable = [
    	'id', 
        'hp_page_id',
        'hp_image_id',
        'hp_id',
        'delete_flg', 
        'create_id', 
        'create_date', 
        'update_id', 
        'update_date',
    ];
    public function hpImage() {
        return $this->belongsTo(HpImage::class, 'hp_image_id');
    }
}