<?php

namespace Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
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
}