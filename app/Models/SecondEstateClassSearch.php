<?php

namespace App\Models;

use App\Traits\MySoftDeletes;
use Library\Custom\Estate\Setting\Second;
use Illuminate\Support\Facades\App;
use App\Collections\SecondEstateClassSearchCollection;

class SecondEstateClassSearch extends Model
{
    use MySoftDeletes;

    protected $table = 'second_estate_class_search';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'hp_id',
        'estate_class',
        'enabled',
        'enabled_estate_type',
        'area_search_filter',
        'search_filter',
        'search_filter_for_bapi',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];
    public function newCollection(array $models = array())
    {
        return new SecondEstateClassSearchCollection($models);
    }
    public function toSettingObject() {
        return new Second($this->toArray());
    }
    
    public function getEnabledEstateTypeArray() {
        return explode(',',$this->enabled_estate_type);
    }
    
    public function isEnabledEstateType($type) {
        return in_array((string)$type, $this->getEnabledEstateTypeArray(), true);
    }

}