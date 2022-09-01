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
    public function run(): void
    {
        TokenCacheProvider::factory()->state([
            'name' => 'lhg_graph',
            'auth_url' => '/oauth2/v2.0/authorize',
            'token_url' => '/oauth2/v2.0/token',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode([
                'tenant' => config('tokencache.azure.client.tenant'),
                'client_id' => config('tokencache.azure.client.client_id'),
                'client_secret' => encrypt(config('tokencache.azure.client.client_secret')),
                'scope' => 'https://graph.microsoft.com/.default',
            ])
        ])->create();

        TokenCacheProvider::factory()->state([
            'name' => 'lhg_arm',
            'auth_url' => '/oauth2/authorize',
            'token_url' => '/oauth2/token',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode([
                'tenant' => config('tokencache.azure.client.tenant'),
                'client_id' => config('tokencache.azure.client.client_id'),
                'client_secret' => encrypt(config('tokencache.azure.client.client_secret')),
                'resource' => 'https://management.azure.com',
            ])
        ])->create();

        TokenCacheProvider::factory()->state([
            'name' => 'lhtest_arm',
            'auth_url' => '/oauth2/authorize',
            'token_url' => '/oauth2/token',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode([
                'tenant' => config('tokencache.azure_test.client.tenant'),
                'client_id' => config('tokencache.azure_test.client.client_id'),
                'client_secret' => encrypt(config('tokencache.azure_test.client.client_secret')),
                'resource' => 'https://management.azure.com',
            ])
        ])->create();
    }
}
