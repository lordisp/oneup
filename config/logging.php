<?php

use App\Logger\DatabaseLogger;

return [

    'deprecations' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),

    'channels' => [
        'db' => [
            'driver' => 'custom',
            'via' => DatabaseLogger::class,
            'level' => env('LOG_LEVEL', 'debug'),
        ],
    ],

];
