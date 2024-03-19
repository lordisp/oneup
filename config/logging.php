<?php

use App\Logger\DatabaseLogger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

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
