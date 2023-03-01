<?php
namespace App\Models;

use Illuminate\Support\Facades\App;
use App\Traits\MySoftDeletes;

 
class HpContact extends Model {
    use MySoftDeletes;

    protected $table = 'hp_contact';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';


    protected $fillable = [
        'id',
        'notification_to_1',
        'notification_to_2',
        'notification_to_3',
        'notification_to_4',
        'notification_to_5',
        'notification_subject',
        'autoreply_flg',
        'autoreply_from',
        'autoreply_sender',
        'autoreply_subject',
        'autoreply_body',
        'heading_code',
        'heading',
        'page_id',
        'hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
}