<?php

namespace Tests\Feature\API\V1;

use App\Models\TokenCacheProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helper;
use Tests\TestCase;

class TokenCacheProviderTest extends TestCase
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        TokenCacheProvider::factory()->count(5)->create();
    }

    /** @test */
    public function cannot_access_api_while_unauthorized()
    {
        $this->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->get('/api/v1/tokencacheprovider')
            ->assertUnauthorized();
    }

    /** @test
     *  TokenCacheProviderController index
     */
    public function can_retrieve_all_provider()
    {
        $token = $this->requestToken()['access_token'];
        $data = $this->withToken($token)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->get('/api/v1/tokencacheprovider')
            ->assertOk();

        $this->assertCount(5, $data->json('data'));
        $this->assertArrayHasKey('auth_url', $data->json('data')[0]);
        $this->assertArrayHasKey('token_url', $data->json('data')[0]);
        $this->assertArrayHasKey('auth_endpoint', $data->json('data')[0]);
        $this->assertArrayHasKey('client', $data->json('data')[0]);
        $this->assertArrayHasKey('tenant', $data->json('data')[0]['client']);
        $this->assertArrayHasKey('client_id', $data->json('data')[0]['client']);
        $this->assertArrayHasKey('scope', $data->json('data')[0]['client']);
    }

    /** @test
     * TokenCacheProviderController store
     */
    public function can_create_new_provider()
    {
        $token = $this->requestToken()['access_token'];
        $client = [
            'tenant' => Str::uuid(),
            'client_id' => Str::uuid(),
            'client_secret' => encrypt(Str::random(40)),
            'scope' => 'https://graph.microsoft.com/.default',
        ];
        $data = [
            'name' => 'Foo',
            'auth_url' => '/foo/bar/baz',
            'token_url' => '/baz/foo/bar',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode($client),
        ];
        $response = $this->withToken($token)
            ->post('/api/v1/tokencacheprovider', $data)
            ->assertStatus(201)
            ->json();
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    /** @test
     * TokenCacheProviderController show
     */
    public function can_retrieve_a_single_provider()
    {
        $provider = TokenCacheProvider::first()->id;
        $token = $this->requestToken()['access_token'];
        $data = $this->withToken($token)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->get('/api/v1/tokencacheprovider/' . $provider)
            ->assertOk();

        $this->assertArrayHasKey('auth_url', $data->json('data')[0]);
        $this->assertArrayHasKey('token_url', $data->json('data')[0]);
        $this->assertArrayHasKey('auth_endpoint', $data->json('data')[0]);
        $this->assertArrayHasKey('client', $data->json('data')[0]);
        $this->assertArrayHasKey('tenant', $data->json('data')[0]['client']);
        $this->assertArrayHasKey('client_id', $data->json('data')[0]['client']);
        $this->assertArrayHasKey('scope', $data->json('data')[0]['client']);
    }

    /** @test
     * TokenCacheProviderController update
     */
    public function can_update_a_provider()
    {
        $token = $this->requestToken()['access_token'];
        $current = TokenCacheProvider::first();

        $client = [
            'tenant' => Str::uuid(),
            'client_id' => Str::uuid(),
            'client_secret' => encrypt(Str::random(40)),
            'scope' => 'https://graph.microsoft.com/.default',
        ];
        $data = [
            'name' => 'Foo',
            'auth_url' => '/foo/bar/baz',
            'token_url' => '/baz/foo/bar',
            'auth_endpoint' => 'https://login.microsoftonline.com/',
            'client' => json_encode($client),
        ];
        $response = $this->withToken($token)
            ->put('/api/v1/tokencacheprovider/' . $current->id, $data)
            ->assertStatus(201)
            ->json();
        $updated = TokenCacheProvider::whereId($current['id'])->first();
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertEquals($current->id, $updated->id);
        $this->assertNotEquals($current->name, $updated->name);
        $this->assertNotEquals($current->auth_url, $updated->auth_url);
        $this->assertNotEquals($current->token_url, $updated->token_url);
        $this->assertNotEquals($current->auth_url, $updated->auth_url);
        $this->assertEquals($current->auth_endpoint, $updated->auth_endpoint);
        $this->assertNotEquals(json_decode($current->client, true), $updated->client);
    }

    /** @test */
    public function can_destroy_a_provider()
    {
        $this->assertDatabaseCount(TokenCacheProvider::class,5);
        $token = $this->requestToken()['access_token'];
        $current = TokenCacheProvider::first();
        $this->withToken($token)
            ->delete('/api/v1/tokencacheprovider/' . $current->id)
            ->assertStatus(200);
        $this->assertDatabaseCount(TokenCacheProvider::class,4);
    }
}
