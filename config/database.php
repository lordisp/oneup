<?php

use Illuminate\Support\Str;

return [

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'oneup'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'sslmode' => 'prefer',
            'options' => env('APP_ENV') != 'local' && env('APP_ENV') != 'testing' ?
                [
                    PDO::MYSQL_ATTR_SSL_CA => base_path().'/ssl/DigiCertGlobalRootCA.crt.pem',
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                ] : [],
        ],
    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => false, // disable to preserve original behavior for existing applications
    ],

    'redis' => [
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'oneup'), '_').'_database_'),
            'persistent' => env('REDIS_PERSISTENT', true),
            'read_timeout' => env('REDIS_READ_TIMEOUT', 10),
            'retry_interval' => env('REDIS_RETRY_INTERVAL', 100),
            'timeout' => env('REDIS_TIMEOUT', 5),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', '6380'),
            'database' => env('REDIS_DB', 0),
            'read_write_timeout' => env('REDIS_READ_WRITE_TIMEOUT', 60),
            'parameters' => [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'cafile' => env('REDIS_CA_CERT', '/var/www/html/ssl/redis/ca.crt'),
                    'local_cert' => env('REDIS_CLIENT_CERT', '/var/www/html/ssl/redis/redis-client.crt'),
                    'local_pk' => env('REDIS_CLIENT_KEY', '/var/www/html/ssl/redis/redis-client.key'),
                ],
            ],
        ],

        'clusters' => [
            'options' => [
                'cluster' => 'redis',
            ],
            'default' => [
                [
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'password' => env('REDIS_PASSWORD', null),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DB', 0),
                ],
                [
                    'host' => env('REDIS_READONLY_HOST', '127.0.0.1'),
                    'password' => env('REDIS_PASSWORD', null),
                    'port' => env('REDIS_PORT', 6379),
                    'database' => env('REDIS_DB', 0),
                    'read_only' => true,
                ],
            ],
        ],
    ],

];
