<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Library\Custom\Estate\Setting\Second;
use App\Casts\AsSubString;

class SecondEstate extends Model
{
    use MySoftDeletes;

    protected $table = 'second_estate';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'applied_start_date',
        'start_date',
        'contract_staff_id',
        'contract_staff_name',
        'contract_staff_department',
        'applied_end_date',
        'end_date',
        'cancel_staff_id',
        'cancel_staff_name',
        'cancel_staff_department',
        'area_search_filter',
        'remarks',
        'delete_flg'
    ];

    protected $casts = [
        'contract_staff_id' => AsSubString::class.':20',
        'cancel_staff_id' => AsSubString::class.':20',
    ];

    /**
     * 利用可能期間内か確認する
     * @return boolean
     */
    public function isAvailable()
    {
        $now = time();

        if (!$this->start_date || strtotime($this->start_date) > $now) {
            return false;
        }

        if ($this->end_date && strtotime(date('Y-m-d', strtotime($this->end_date))) + 86400 < $now) {
            return false;
        }
        return true;
    }

    /**
     * 設定オブジェクトを取得する
     * @return Library\Custom\Estate\Setting\Second
     */
    public function toSettingObject()
    {
        return new Second($this->toArray());
    }
}
