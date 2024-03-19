<?php

namespace Tests;

use App\Http\Livewire\PCI\FirewallRequestsImport;
use App\Models\ClientScope;
use App\Models\Passport\Client;
use App\Models\Scope;
use App\Models\TokenCacheProvider;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Livewire\Livewire;
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
            'redirect' => config('app.url').'/callback',
            '_token' => csrf_token(),
        ]);
    }

    protected function requestToken(string $scopes = 'subnets-create'): string
    {
        [$client, $scope] = $this->getPassportClientWithScopes($scopes);

        return $this->createPassportClientToken(
            $client->id,
            $client->secret,
            $scope->scope
        );
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

    protected function createPassportClient(?string $name = null): \Laravel\Passport\Client
    {
        $name = $name ?: config('app.name');

        Passport::$hashesClientSecrets = false;

        $this->artisan(
            'passport:client',
            ['--name' => $name, '--client' => null]
        );

        $client = DB::table('oauth_clients')
            ->select(['id'])
            ->where('name', '=', $name)
            ->where('personal_access_client', '=', false)
            ->where('password_client', '=', false)
            ->where('redirect', '=', '')
            ->where('provider', '=', null)
            ->where('user_id', '=', null)
            ->first();

        return Client::whereId($client->id)->first();
    }

    protected function createPassportClientToken($clientId, $secret, $scope = '*')
    {
        $response = $this->post('/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $secret,
            'scope' => $scope,
        ]);
        if ($response->isSuccessful()) {
            return $response->json()['access_token'];
        }

        return response(status: $response->status());
    }

    public function getPassportClientWithScopes(string $scope): array
    {
        $client = $this->createPassportClient();
        $scope = Scope::firstOrCreate(['scope' => $scope]);
        $client->clientScopes()->attach($scope->id);
        ClientScope::whereClientScope($client->id, $scope->id)
            ->approveScope(User::factory()->create());

        return [$client, $scope];
    }

    protected function getStub(string $name)
    {
        return json_decode(file_get_contents(base_path().'/tests/Feature/Stubs/'.$name), true);
    }

    protected function importOneFile(string $file = '')
    {
        $file = ! empty($file) ? $file : 'valid.json';
        Storage::fake('tmp-for-tests');
        $first = file_get_contents(base_path().'/tests/Feature/Stubs/firewallImport/'.$file);
        $files[] = UploadedFile::fake()->createWithContent('file.json', $first);

        $user = User::first();
        $user->assignRole('Firewall Administrator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->set('attachments', $files)
            ->assertHasNoErrors()
            ->call('save');
    }

    protected function getFakeToken(): array
    {
        return [
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/Feature/Services/stubs/provider_lhg_arm_token_response.json'), true)),
        ];
    }
}
