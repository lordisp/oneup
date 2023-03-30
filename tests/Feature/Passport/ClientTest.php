<?php

namespace Tests\Feature\Passport;

use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Passport\TokenRepository;
use Tests\Helper;
use Tests\TestCase;
use function PHPUnit\Framework\classHasAttribute;

class ClientTest extends TestCase
{
    use RefreshDatabase, Helper;

    /** @test */
    public function cannot_access_create_client_as_guest()
    {
        $this->post('/oauth/clients')->assertRedirect('/login');
    }

    /** @test */
    public function api_returns_validation_errors()
    {
        $user = User::factory()->create();

        $client = $this->actingAs($user)->post('/oauth/clients');

        $client->assertSessionHasErrors(['redirect', 'name']);
    }

    /** @test */
    public function user_can_create_a_client_while_logged_in()
    {
        $user = User::factory()->create();
        $client = $this->actingAs($user)->post('/oauth/clients', [
            'name' => 'Test Client',
            'redirect' => 'http://localhost/callback',
        ]);

        $client->assertOk();

        $this->assertArrayHasKey('plainSecret', $client->json());
        $this->assertArrayHasKey('user_id', $client->json());
        $this->assertArrayHasKey('name', $client->json());
        $this->assertArrayHasKey('provider', $client->json());
        $this->assertArrayHasKey('redirect', $client->json());
        $this->assertArrayHasKey('redirect', $client->json());
        $this->assertArrayHasKey('personal_access_client', $client->json());
        $this->assertArrayHasKey('password_client', $client->json());
        $this->assertArrayHasKey('revoked', $client->json());
        $this->assertArrayHasKey('id', $client->json());
        $this->assertArrayHasKey('updated_at', $client->json());
        $this->assertArrayHasKey('created_at', $client->json());

        $this->assertTrue(Str::isUuid($client->json('id')));
        $this->assertTrue(Str::isUuid($client->json('user_id')));
        $this->assertEquals($user->getAuthIdentifier(), $client->json('user_id'));
        $this->assertNull($client->json('provider'));
        $this->assertFalse($client->json('personal_access_client'));
        $this->assertFalse($client->json('password_client'));
        $this->assertFalse($client->json('revoked'));
    }

    /** @test
     * @depends  user_can_create_a_client_while_logged_in
     */
    public function can_request_an_access_token()
    {
        list($client, $scope) = $this->getPassportClientWithScopes('subnets-create');

        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => $scope->scope,
        ];

        $response = $this->post(
            '/oauth/token', $data, ['content-type' => 'application/x-www-form-urlencoded']
        );

        $response->assertStatus(200);

        $this->assertCount(3, $response->json());

        $this->assertEquals('Bearer', $response->json()['token_type']);

        $this->assertEquals(3600, $response->json()['expires_in']);

        $this->assertIsString($response->json()['access_token']);

        return $response->json()['access_token'];

    }

    /** @test
     * @depends can_request_an_access_token
     */
    public function can_revoke_access_token()
    {
        $this->requestToken();

        $tokenId = DB::table('oauth_access_tokens')->first()->id;

        $tokenRepository = app(TokenRepository::class);

        $status = $tokenRepository->revokeAccessToken($tokenId);

        $this->assertEquals(1, $status);
    }

    /** @test
     * @depends can_request_an_access_token
     */
    public function can_decode_jwt($token)
    {
        $decoded = app()->accessor::jwt_decode($token);

        $this->assertArrayHasKey('aud', get_object_vars($decoded));
        $this->assertArrayHasKey('jti', get_object_vars($decoded));
        $this->assertArrayHasKey('iat', get_object_vars($decoded));
        $this->assertArrayHasKey('nbf', get_object_vars($decoded));
        $this->assertArrayHasKey('exp', get_object_vars($decoded));
        $this->assertArrayHasKey('sub', get_object_vars($decoded));
        $this->assertArrayHasKey('scopes', get_object_vars($decoded));
    }


    /** @test
     * @throws BindingResolutionException
     */
    public function passports_run_hourly()
    {
        $schedule = app()->make(Schedule::class);

        $events = collect($schedule->events())->filter(function (Event $event) {
            return stripos($event->command, 'passport:purge');
        });

        if ($events->count() == 0) {
            $this->fail('No events found');
        }

        $events->each(function (Event $event) {
            $this->assertEquals('0 * * * *', $event->expression);
        });
    }
}
