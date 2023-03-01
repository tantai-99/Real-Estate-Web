<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['custom_error', 'custom_emergency', 'custom_alert', 'custom_critical', 'custom_warning', 'custom_notice', 'custom_debug', 'custom_info'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
        'debug' => [
            'driver' => 'single',
            'path' => storage_path('logs/debug.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'publish' => [
            'driver' => 'single',
            'path' => storage_path('logs/publish.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'publish_render' => [
            'driver' => 'single',
            'path' => storage_path('logs/publish_render.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_ChangeCmsPlan_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_ChangeCmsPlan_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_ChangeCmsPlan_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_ChangeCmsPlan_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_AssessHomePages_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_AssessHomePages_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_AssessHomePages_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_AssessHomePages_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CleanContactLog_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CleanContactLog_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CleanContactLog_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CleanContactLog_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateCompanyContactLog_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateCompanyContactLog_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateCompanyContactLog_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateCompanyContactLog_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CleanSecondEstateExclusionCom_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CleanSecondEstateExclusionCom_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CleanSecondEstateExclusionCom_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CleanSecondEstateExclusionCom_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateCompanyCsv_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateCompanyCsv_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateCompanyCsv_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateCompanyCsv_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateConversionLog_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateConversionLog_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateConversionLog_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateConversionLog_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateEstateContactLog_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateEstateContactLog_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateEstateContactLog_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateEstateContactLog_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateEstateParentChildCsv_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateEstateParentChildCsv_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateEstateParentChildCsv_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateEstateParentChildCsv_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateEstateSettingCsv_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateEstateSettingCsv_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateEstateSettingCsv_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateEstateSettingCsv_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateParentChildCsv_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateParentChildCsv_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateParentChildCsv_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateParentChildCsv_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_TopOriginalEvent_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_TopOriginalEvent_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_TopOriginalEvent_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_TopOriginalEvent_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_SitemapXml_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_SitemapXml_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_SitemapXml_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_SitemapXml_info.log'),
		    'level' => env('LOG_LEVEL', 'debug'),
        ],
		'Batch_CreateSecondEstatePrefCsv_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateSecondEstatePrefCsv_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_CreateSecondEstatePrefCsv_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_CreateSecondEstatePrefCsv_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_Diacrisis_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_Diacrisis_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_Diacrisis_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_Diacrisis_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_FDPEvent_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_FDPEvent_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_FDPEvent_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_FDPEvent_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_InitialSetting_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_InitialSetting_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_InitialSetting_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_InitialSetting_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_InitialSysImages_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_InitialSysImages_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_InitialSysImages_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_InitialSysImages_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_PublishReserve_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_PublishReserve_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_PublishReserve_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_PublishReserve_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_NotifyPublishResult_error' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_NotifyPublishResult_error.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'Batch_NotifyPublishResult_info' => [
            'driver' => 'single',
            'path' => storage_path('logs/Batch_NotifyPublishResult_info.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'commit' => [
            'driver' => 'single',
            'path' => storage_path('logs/commit.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'custom_error' => [
            'driver' => 'single',
            'level'  => 'error',
            'path'   => storage_path('logs/error.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_emergency' => [
            'driver' => 'single',
            'level'  => 'emergency',
            'path'   => storage_path('logs/error.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_alert' => [
            'driver' => 'single',
            'level'  => 'alert',
            'path'   => storage_path('logs/error.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_critical' => [
            'driver' => 'single',
            'level'  => 'critical',
            'path'   => storage_path('logs/error.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_warning' => [
            'driver' => 'single',
            'level'  => 'warning',
            'path'   => storage_path('logs/error.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_notice' => [
            'driver' => 'single',
            'level'  => 'notice',
            'path'   => storage_path('logs/error.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_debug' => [
            'driver' => 'single',
            'level'  => 'debug',
            'path'   => storage_path('logs/debug.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
        'custom_info' => [
            'driver' => 'single',
            'level'  => 'info',
            'path'   => storage_path('logs/debug.log'),
            'bubble' => false,
            'formatter' => \Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %channel%.%level_name%: %message%\n",
            ],
        ],
    ],

];
