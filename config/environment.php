<?php
$ip = [];
$ip_deny = [];
if (count(explode(',', env('IP'))) > 1) {
    $ip = ['ip' => explode(',', env('IP'))];
}
if (count(explode(',', env('IP_DENY'))) > 1) {
    $ip_deny = ['ip' => explode(',', env('IP_DENY'))];
}
$kaiin = [];
if (env('KAIIN_DUMMY_KAPI')) {
    $kaiin = [
        'kaiin_base_url' => env('KAIIN_BASE_URL'),
        'dummy_kapi' => env('KAIIN_DUMMY_KAPI')
    ];
} else {
    $kaiin = [
        'kaiin_base_url' => env('KAIIN_BASE_URL')
    ];
}
return [
    'api' => [
        'api' => [
            'domain' => env('API_DOMAIN')
        ]
    ],
    'cms' => [
        'header' => [
            'mark' => [
                'class' => env('CMS_HEADER_CLASS'),
                'label' => base64_decode(env('CMS_HEADER_LABEL'))
            ]
        ]
    ],
    'commit' => [
        'commit' => [
            'ips' => explode(',', env('COMMIT_IPS'))
        ]
    ],
    'console_log' => [
        'dev' => (int)env('CONSOLE_LOG_DEV')
    ],
    'fileUploadServer' => [
        'upload' => [
            'url' => env('FILE_UPLOAD_URL'),
            'admin_url' => env('FILE_UPLOAD_ADMIN_URL'),
            'ftp' => [
                'kaiathp' => [
                    'url' => env('FILE_UPLOAD_FTP_URL'),
                    'id' => env('FILE_UPLOAD_FTP_ID'),
                    'password' => env('FILE_UPLOAD_FTP_PASSWORD'),
                    'dir' => env('FILE_UPLOAD_FTP_DIR')
                ]
            ]
        ]
    ],
    'google' => [
        'map' => [
            'api' => [
                'key' => env('GOOGLE_MAP_API_KEY'),
                'id' => [
                    'usersite' => env('GOOGLE_MAP_API_ID')
                ],
                'channel' => env('GOOGLE_MAP_API_CHANNEL'),
                'channel_for_demo' => env('GOOGLE_MAP_API_CHANNEL_FOR_DEMO')
            ]
        ]
    ],
    'ip' => $ip,
    'ip_deny' => $ip_deny,
    'kaiin_api' => $kaiin,
    'kk_api' => [
        'api' => [
            'url' => env('KK_API_URL')
        ],
        'auth' => [
            'url' => env('KK_AUTH_URL')
        ]
    ],
    'publish' => [
        'publish' => [
            'lock_key_prefix' => env('PUBLISH_LOCK_KEY_PREFIX'),
            // 公開処理結果通知バッチ排他制御用ロックキー
            'notify_lock_key' => env('PUBLISH_NOTIFY_LOCK_KEY'),
            // ロック処理待ち時間(s)
            'lock_wait' => (int)env('PUBLISH_LOCK_WAIT'),
            // 公開処理エラーメッセージ
            'exclusive_error_msg' => base64_decode(env('PUBLISH_EXCLUSIVE_ERROR_MSG')),
            // 公開処理異常終了判定時間(min)
            'timeout' => (int)env('PUBLISH_TIMEOUT'),
            'env_jp' => env('PUBLISH_ENV_JP'),
            'mail_from' => env('PUBLISH_MAIL_FROM'),
            'mail_tos' => explode(',', env('PUBLISH_MAIL_TOS')),
        ]
    ],
    'sales_demo' => [
        'demo' => [
            'domain' => env('SALES_DEMO_DEMO_DOMAIN')
        ],
        'api'=>[
            'key' => env('SALES_DEMO_API_KEY')
        ]
    ],
    'theme' => [
        'display_all' => (int)env('THEME_DISPLAY_ALL')
    ],
    'display_exceptions' => env('DISPLAY_EXCEPTIONS'),
    'path_data_batch' => env('BATCH_PATH_DATA')
];