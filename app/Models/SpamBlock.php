<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class SpamBlock extends Model
{
    use MySoftDeletes;

    protected $table = 'spam_blocks';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

 
    protected $fillable = [
        'id ',
        'email',
        'email_option',
        'tel',
        'range_option',
        'delete_flg',
        'create_date',
        'update_date',
    ];

    public function companySpamBlock() {
        return $this->hasMany(CompanySpamBlock::class, 'spam_block_id');
    }

}
