<?php
namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Traits\MySoftDeletes;
 
class AssociatedHpPageAttribute extends Model {
    use MySoftDeletes;

    protected $table = 'associated_hp_page_attribute';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';


    protected $fillable = [
        'id',
        'hp_page_id',
        'hp_main_parts_id',
        'hp_id',
        'delete_flg',
        'create_date',
        'update_date',
    ];
}