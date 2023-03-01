<?php
$api = [];
if (env('V1API_API_DUMMY_KAPI')) {
    $api = [
        'base_url' => env('V1API_API_BASE_URL'),
        'kaiin_base_url' => env('V1API_API_KAIIN_BASE_URL'),
        'img_server' => env('V1API_API_IMG_SERVER'),
        'dummy_kaiin_link_no' => env('V1API_API_DUMMY_KAIIN_LINK_NO'),
        'dummy_kapi' => env('V1API_API_DUMMY_KAPI'),
        'dummy_bapi_group_id' => env('V1API_API_DUMMY_BAPI_GROUP_ID'),
        'img_can_use_https' => env('V1API_API_IMG_CAN_USE_HTTPS')
    ];
} else {
    $api = [
        'base_url'       => env('V1API_API_BASE_URL'),
        'kaiin_base_url' => env('V1API_API_KAIIN_BASE_URL'),
        'img_server' => env('V1API_API_IMG_SERVER'),
        'img_can_use_https' => env('V1API_API_IMG_CAN_USE_HTTPS'),
    ];
}
return [
    'api' => $api,
    'debug' => [
        'usersite_use_estate_setting_flg' => (int)env('V1API_DEBUG_USERSITE_USE_ESTATE_SETTING_FLG')
    ]
];