<?php
return [
    'contact' => [
        'contact' => [
            'mail' => [
                'format' => [
                    'from' => env('CONTACT_MAIL_FORMAT_FROM')
                ]
            ],
            'api' => [
                'url' => env('CONTACT_API_URL'), 
                'estaterequesturl' => env('CONTACT_API_ESTATEREQUESTURL'), 
                'estateurl' => env('CONTACT_API_ESTATEURL'),
                'KokyakuKanriKeiyaku' => [
                    'url' => env('CONTACT_API_URL_KEIYAKU_URL') 
                ]
            ]
        ]
    ]
];