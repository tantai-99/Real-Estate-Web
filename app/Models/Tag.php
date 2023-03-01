<?php

namespace App\Models;

use App\Traits\MySoftDeletes;

class Tag extends Model
{
    use MySoftDeletes;

    protected $table = 'tag';
    public $timestamps = false;
    const DELETED_AT = 'delete_flg';

    protected $fillable = [
        'id',
        'company_id',
        'google_api_key',
        'google_user_id',
        'google_password',
        'google_analytics_mail',
        'google_p12',
        'google_analytics_view_id',
        'google_analytics_code',
        'above_close_head_tag',
        'under_body_tag',
        'above_close_body_tag',
        'above_close_head_tag_contact_thanks',
        'under_body_tag_contact_thanks',
        'above_close_body_tag_contact_thanks',
        'above_close_head_tag_assess_thanks',
        'under_body_tag_assess_thanks',
        'above_close_body_tag_assess_thanks',
        'above_close_head_tag_request_thanks',
        'under_body_tag_request_thanks',
        'above_close_body_tag_request_thanks',
        'above_close_head_tag_contact_input',
        'under_body_tag_contact_input',
        'above_close_body_tag_contact_input',
        'above_close_head_tag_assess_input',
        'under_body_tag_assess_input',
        'above_close_body_tag_assess_input',
        'above_close_head_tag_request_input',
        'under_body_tag_request_input',
        'above_close_body_tag_request_input',
        'all_tags',
        'delete_flg',
        'create_id',
        'create_date',
        'update_id',
        'update_date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
