<?php

namespace Tests;

use App\Models\TokenCacheProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use ReflectionClass;
use ReflectionException;
use stdClass;

trait Helper
{
    protected function createClient(): TestResponse
    {
        $user = User::factory()->create()->first();

        return $this->actingAs($user)->post('/oauth/clients', [
            'name' => 'TestClient',
            'redirect' => config('app.url') . '/callback',
            '_token' => csrf_token(),
        ]);
    }

    protected function requestToken(): TestResponse
    {
        $client = $this->createClient();

        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $client['id'],
            'client_secret' => $client['plainSecret'],
            'scope' => '*',
        ];

        return $this->post('/oauth/token', $data, ['content-type' => 'application/x-www-form-urlencoded']);
    }

    protected function createPersonalClient(): Model|Builder|stdClass|null
    {
        Passport::$hashesClientSecrets = false;

        $this->artisan(
            'passport:client',
            ['--name' => config('app.name'), '--personal' => null]
        );

        // use the query builder instead of the model, to retrieve the client secret
        return DB::table('oauth_clients')
            ->where('personal_access_client', '=', true)
            ->first();
    }

    /**
     * @throws ReflectionException
     */
    protected function accessProtected($obj, $prop)
    {
        $property = (new ReflectionClass($obj))->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    protected function makeAlaProvider()
    {
        return TokenCacheProvider::factory()->state([
            'name' => 'webhook_log_analytics',
            'auth_url' => '/oauth2/authorize',
            'token_url' => '/oauth2/token',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode([
                'tenant' => config('tokencache.azure.client.tenant'),
                'client_id' => config('tokencache.azure.client.client_id'),
                'client_secret' => encrypt(config('tokencache.azure.client.client_secret')),
                'resource' => 'https://api.loganalytics.io',
            ]),
        ])->create()->name;
    }

    protected function getStub(string $name)
    {
        return json_decode(file_get_contents(base_path() . '/tests/Feature/Stubs/' . $name), true);
    }
}