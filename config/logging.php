<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    /*
     * Defaults to "daily" rather than Laravel's "stack"/"single": a single
     * laravel.log grows without limit, and on shared hosting nobody prunes
     * it. The daily driver writes laravel-YYYY-MM-DD.log and deletes files
     * older than LOG_DAILY_DAYS on the first write of each new day — no cron
     * involved. Set LOG_CHANNEL in .env to override.
     */
    'default' => env('LOG_CHANNEL', 'daily'),

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

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            // 'daily', not 'single' — otherwise anything still pointed at the
            // stack channel keeps writing to one unbounded file.
            'channels' => explode(',', env('LOG_STACK', 'daily')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        /*
         * "custom" rather than Laravel's "daily" driver: the date rotation is
         * the same (App\Logging\SizeCappedFileHandler extends Monolog's
         * RotatingFileHandler), but it also truncates the file at a 10MB hard
         * cap. Rotating by date alone cannot stop one bad day — an error
         * inside a loop, or a warning on every crawler request — from filling
         * the account's disk quota before midnight.
         */
        'daily' => [
            'driver' => 'custom',
            'via' => \App\Logging\CreateSizeCappedLogger::class,
            'name' => 'laravel',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            // Number of daily files kept. 1 = today only; the previous day's
            // file is deleted as soon as today's is created.
            'days' => env('LOG_DAILY_DAYS', 7),
            'replace_placeholders' => true,
        ],

        /*
         * Slow-request diagnostics (App\Http\Middleware\LogSlowRequests).
         * Separate file so a flood of slow-page lines cannot push real errors
         * out of laravel.log, and capped by the same handler.
         */
        'slow' => [
            'driver' => 'custom',
            'via' => \App\Logging\CreateSizeCappedLogger::class,
            'name' => 'slow',
            'path' => storage_path('logs/slow-requests.log'),
            'level' => 'debug',
            'days' => env('LOG_DAILY_DAYS', 7),
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
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
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
