<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resourcegraph' => [
        'expire' => env('ARM_CACHE_EXPIRE', 540),
    ],

    'pdns' => [
        'chunk' => [
            'zones' => env('PDNS_CHUNK_ZONES', 10),
            'records' => env('PDNS_CHUNK_RECORDS', 100),
        ]
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
        ]
    ],

    'azure-ad' => [
        'dismis-risky-users' => env('DISMIS_RISKY_USERS', true)
    ]
];
