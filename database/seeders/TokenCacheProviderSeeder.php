<?php

namespace Database\Seeders;

use App\Models\TokenCacheProvider;
use Illuminate\Database\Seeder;

class TokenCacheProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TokenCacheProvider::factory()->state([
            'name' => 'azure_ad',
            'auth_url' => '/oauth2/v2.0/authorize',
            'token_url' => '/oauth2/v2.0/token',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode([
                'tenant' => env('AZURE_TENANT'),
                'client_id' => env('AZURE_CLIENT_ID'),
                'client_secret' => encrypt(env('AZURE_CLIENT_SECRET')),
                'scope' => 'https://graph.microsoft.com/.default',
            ])
        ])->create();
        TokenCacheProvider::factory()->state([
            'name' => 'azure',
            'auth_url' => '/oauth2/authorize',
            'token_url' => '/oauth2/token',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode([
                'tenant' => env('AZURE_TENANT'),
                'client_id' => env('AZURE_CLIENT_ID'),
                'client_secret' => encrypt(env('AZURE_CLIENT_SECRET')),
                'resource' => 'https://management.azure.com/',
            ])
        ])->create();
    }
}
