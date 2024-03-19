<?php

return [

    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'oneup@dlh.de'),
                'name' => env('MAIL_FROM_NAME', 'ONEUP'),
            ],
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],
    ],

];
