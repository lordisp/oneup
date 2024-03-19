<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'resourcegraph' => [
        'expire' => env('ARM_CACHE_EXPIRE', 540),
    ],

    'pdns' => [
        'chunk' => [
            'zones' => env('PDNS_CHUNK_ZONES', 10),
            'records' => env('PDNS_CHUNK_RECORDS', 100),
        ],
    ],

    'scheduler' => [
        'prune-batches' => [
            'hours' => env('PRUNE_BATCHES_HOURS', 48),
            'cancelled' => env('PRUNE_BATCHES_CANCELLED', 72),
            'unfinished' => env('PRUNE_BATCHES_UNFINISHED', 72),
        ],

        'prune-failed' => [
            'hours' => env('PRUNE_FAILED_HOURS', 24),
        ],

        'prune-telescope' => [
            'hours' => env('PRUNE_TELESCOPE_HOURS', 12),
        ],

        'vm-start-stop-scheduler' => [
            'enabled' => env('VM_START_STOP_ENABLED', true),
            'timezone' => env('VM_START_STOP_TIMEZONE', 'Europe/Amsterdam'),
        ],
    ],

    'azure-ad' => [
        'dismiss-risky-users' => env('DISMISS_RISKY_USERS', true),
        'chunk-dismiss-risky-users' => env('CHUNK_DISMISS_RISKY_USERS', 20),
    ],

];
