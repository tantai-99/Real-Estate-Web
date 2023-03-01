<?php namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Traits\MySoftDeletes;

 
class MTheme extends Model{
    use MySoftDeletes;

    protected $table = 'm_theme';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
 
    protected $fillable = [
        'id',
        'name',
        'title',
        'plan_advance',
        'plan_standard',
        'plan_lite',
        'language',
        'custom_flg',
        'view_sort',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hp() {
        return $this->hasMany(Hp::class);
    }
}