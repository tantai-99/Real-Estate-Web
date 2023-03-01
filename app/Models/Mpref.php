<?php namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Traits\MySoftDeletes;

 
class Mpref extends Model{
    use MySoftDeletes;

    protected $table = 'm_pref';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';
 
    protected $fillable = [
        'id',
        'pref_code',
        'pref_name',
        'pref_url',
        'area_category_id',
        'delete_flg'
    ];
}