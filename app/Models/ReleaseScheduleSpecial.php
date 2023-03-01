<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use App\Collections\ReleaseScheduleSpecialCollection;

class ReleaseScheduleSpecial extends Model
{
    use MySoftDeletes;

    protected $table = 'release_schedule_special';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'hp_id',
        'special_estate_id',
        'release_type_code',
        'release_at',
        'completion_flg',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function hp()
    {
        return $this->belongsTo(Hp::class, 'hp_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function newCollection(array $models = Array()) {
		return new ReleaseScheduleSpecialCollection($models);
	}
}
