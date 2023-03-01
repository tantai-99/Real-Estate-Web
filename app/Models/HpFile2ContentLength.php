<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HpFile2ContentLength extends Model
{
    use HasFactory;

    protected $table = 'hp_file2_content_length';

    public $timestamps = false;

    protected $fillable = [
        'aid',
        'hp_file2_content_id',
        'content_length',
        'create_date', 
        'update_date',
    ];
    public function hpFile2Content() {
        return $this->belongsTo(HpFile2Content::class, 'hp_file2_content_id');
    }
}