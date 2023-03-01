<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class EstateTag extends Model
{
    use MySoftDeletes;

    protected $table = 'estate_tag';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'above_close_head_tag_residential_rental_thanks',
        'under_body_tag_residential_rental_thanks',
        'above_close_body_tag_residential_rental_thanks',
        'above_close_head_tag_business_rental_thanks',
        'under_body_tag_business_rental_thanks',
        'above_close_body_tag_business_rental_thanks',
        'above_close_head_tag_residential_sale_thanks',
        'under_body_tag_residential_sale_thanks',
        'above_close_body_tag_residential_sale_thanks',
        'above_close_head_tag_business_sale_thanks',
        'under_body_tag_business_sale_thanks',
        'above_close_body_tag_business_sale_thanks',
        'above_close_head_tag_residential_rental_input',
        'under_body_tag_residential_rental_input',
        'above_close_body_tag_residential_rental_input',
        'above_close_head_tag_business_rental_input',
        'under_body_tag_business_rental_input',
        'above_close_body_tag_business_rental_input',
        'above_close_head_tag_residential_sale_input',
        'under_body_tag_residential_sale_input',
        'above_close_body_tag_residential_sale_input',
        'above_close_head_tag_business_sale_input',
        'under_body_tag_business_sale_input',
        'above_close_body_tag_business_sale_input',
        'all_tags',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
