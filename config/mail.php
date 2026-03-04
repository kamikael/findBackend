<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Address
    |--------------------------------------------------------------------------
    */
    'admin_address' => env('MAIL_ADMIN_ADDRESS'),

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    */
    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */
    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',

            // Optionnel (si tu utilises MAIL_URL)
            'url' => env('MAIL_URL'),

            // Optionnel (laisse null la plupart du temps)
            'scheme' => env('MAIL_SCHEME'),

            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),

            // IMPORTANT : encryption
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),

            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),

            'timeout' => null,

            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)
            ),

            // Symfony Mailer option (dsn): false desactive verify_peer et verify_peer_name
            'verify_peer' => env('MAIL_VERIFY_PEER', true),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

];
