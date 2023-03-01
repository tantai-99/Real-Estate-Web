<?php
namespace App\Models;

use App\Traits\MySoftDeletes;
 
class HpHtmlContent extends Model {
    use MySoftDeletes;

    protected $table = 'hp_html_content';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'content',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hp() {
        $this->belongsTo(Hp::class, 'hp_id');
    }
}