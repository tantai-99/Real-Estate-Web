<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class AssociatedCompanyHp extends Model
{
    use MySoftDeletes;

    protected $table = 'associated_company_hp';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'company_id',
        'current_hp_id',
        'space_hp_id',
        'backup_hp_id',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',

    ];

    protected $primaryKey = 'company_id';

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function logDelete()
    {
        return $this->hasMany(LogDelete::class, 'hp_id');
    }
    public function hp($col)
    {
        return $this->belongsTo(Hp::class, $col);
    }
}