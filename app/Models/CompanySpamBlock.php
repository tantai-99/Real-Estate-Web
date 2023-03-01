<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class CompanySpamBlock extends Model
{
    use MySoftDeletes;

    protected $table = 'company_spam_block';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id ',
        'company_id',
        'spam_block_id',
        'delete_flg',
        'create_date',
        'update_date',
    ];

    public function spamBlock() {
        return $this->belongsTo(SpamBlock::class, 'spam_block_id');
    }

}
