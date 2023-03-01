<?php
$ip = ["ip" => []];
if (count(explode(',', env('ADMIN_IP'))) > 1) {
    $ip = ['ip' => explode(',', env('ADMIN_IP'))];
}
return [
    'company' => [
        'company' =>
        [
            'ftp' => [
                'server_name' => env('ADMIN_COMPANY_FTP_SERVER_NAME'),
                'password' => env('ADMIN_COMPANY_FTP_PASSWORD'),
                'port' => (int)env('ADMIN_COMPANY_FTP_PORT'),
            ],
            'controlpanel' => [
                'url' => env('ADMIN_COMPANY_CONTROLPANEL_URL')
            ],
        ],
        'backbone' =>
        [
            'api' =>
            [
                'member' => [
                    'rapi' => [
                        'url' => env('ADMIN_BACKBONE_API_MEMBER_RAPI_URL')
                    ],
                    'url' => env('ADMIN_BACKBONE_API_MEMBER_URL')
                ],
                'staff' => [
                    'rapi' => [
                        'url' => env('ADMIN_BACKBONE_API_STAFF_RAPI_URL')
                    ],
                    'url' => env('ADMIN_BACKBONE_API_STAFF_URL')
                ],
            ]
        ]
    ],
    'FileUploadServer' => [
        'upload' => [
            'url' => env('ADMIN_FILEUPLOADSERVER_URL'),
            'admin_url' => env('ADMIN_FILEUPLOADSERVER_ADMIN_URL'),
            'ftp' => [
                'kaiathp' => [
                    'url' => env('ADMIN_FILEUPLOADSERVER_FTP_URL'),
                    'id' => env('ADMIN_FILEUPLOADSERVER_FTP_ID'),
                    'password' => env('ADMIN_FILEUPLOADSERVER_FTP_PASSWORD'),
                    'dir' => env('ADMIN_FILEUPLOADSERVER_FTP_DIR')
                ]
            ]
        ]
    ],
    'ip' => $ip,
];