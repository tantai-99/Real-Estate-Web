<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class EstateRequestTag extends Model
{
    use MySoftDeletes;

    protected $table = 'estate_request_tag';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'above_close_head_tag_residential_rental_request_thanks',
        'under_body_tag_residential_rental_request_thanks',
        'above_close_body_tag_residential_rental_request_thanks',
        'above_close_head_tag_business_rental_request_thanks',
        'under_body_tag_business_rental_request_thanks',
        'above_close_body_tag_business_rental_request_thanks',
        'above_close_head_tag_residential_sale_request_thanks',
        'under_body_tag_residential_sale_request_thanks',
        'above_close_body_tag_residential_sale_request_thanks',
        'above_close_head_tag_business_sale_request_thanks',
        'under_body_tag_business_sale_request_thanks',
        'above_close_body_tag_business_sale_request_thanks',
        'above_close_head_tag_residential_rental_request_input',
        'under_body_tag_residential_rental_request_input',
        'above_close_body_tag_residential_rental_request_input',
        'above_close_head_tag_business_rental_request_input',
        'under_body_tag_business_rental_request_input',
        'above_close_body_tag_business_rental_request_input',
        'above_close_head_tag_residential_sale_request_input',
        'under_body_tag_residential_sale_request_input',
        'above_close_body_tag_residential_sale_request_input',
        'above_close_head_tag_business_sale_request_input',
        'under_body_tag_business_sale_request_input',
        'above_close_body_tag_business_sale_request_input',
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
