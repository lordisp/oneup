<?php

return [

    /* TOKEN_CACHE_ENCRYPT
     * Token will be cached and returned encrypted by default
     * You may change this behaviour by set the TOKEN_CACHE_DRIVER env to false
     * or call the TokenCache::withoutEncryption() facade.
     * The token will still encrypt stored in the cached but decrypted returned
     * */

    'encrypt' => env('TOKEN_CACHE_ENCRYPT', true),
    'default' => env('TOKEN_CACHE_PROVIDER', 'azure_ad'),

    // possible values are 'database' or 'environment'
    'source' => env('TOKEN_CACHE_SOURCE', 'database'),

    'provider' => [
        'azure' => [
            'client' => [
                'tenant' => env('AZURE_TENANT'),
                'client_id' => env('AZURE_CLIENT_ID'),
                'client_secret' => env('AZURE_CLIENT_SECRET'),
                'resource' => 'https://management.azure.com/',
            ],
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'token_url' => env('AZURE_TOKEN_URL', '/oauth2/token'),
        ],
        'azure_ad' => [
            'client' => [
                'tenant' => env('AZURE_AD_TENANT', 'common'),
                'client_id' => env('AZURE_AD_CLIENT_ID'),
                'client_secret' => env('AZURE_AD_CLIENT_SECRET'),
                'scope' => env('AZURE_AD_SCOPE', 'https://graph.microsoft.com/.default'),
            ],
            'auth_url' => env('AZURE_AUTH_URL', '/oauth2/v2.0/authorize'),
            'token_url' => env('AZURE_TOKEN_URL', '/oauth2/v2.0/token'),
            'auth_endpoint' => 'https://login.microsoftonline.com/',
        ],
    ]

];
