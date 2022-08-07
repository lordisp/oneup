<?php

namespace Tests\Feature\API\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase, Helper;

    public function testShow()
    {
        $response = $this->withToken(
            $this->requestToken()->json('access_token')
        )->get('/api/v1/users');

        $response->assertOk();

        $this->assertArrayHasKey('data', $response->json());
        $this->assertArrayHasKey('id', $response->json('data')[0]);
        $this->assertArrayHasKey('email', $response->json('data')[0]);
        $this->assertArrayHasKey('created_at', $response->json('data')[0]);
        $this->assertArrayHasKey('updated_at', $response->json('data')[0]);

        $this->assertArrayHasKey('links', $response->json());
        $this->assertArrayHasKey('first', $response->json('links'));
        $this->assertArrayHasKey('last', $response->json('links'));
        $this->assertArrayHasKey('prev', $response->json('links'));
        $this->assertArrayHasKey('next', $response->json('links'));
        $this->assertArrayHasKey('meta', $response->json());
        $this->assertArrayHasKey('current_page', $response->json('meta'));
        $this->assertArrayHasKey('from', $response->json('meta'));
        $this->assertArrayHasKey('last_page', $response->json('meta'));
        $this->assertArrayHasKey('links', $response->json('meta'));
        $this->assertArrayHasKey('url', $response->json('meta.links')[0]);
        $this->assertArrayHasKey('label', $response->json('meta.links')[0]);
        $this->assertArrayHasKey('active', $response->json('meta.links')[0]);
        $this->assertArrayHasKey('path', $response->json('meta'));
        $this->assertArrayHasKey('per_page', $response->json('meta'));
        $this->assertArrayHasKey('to', $response->json('meta'));
        $this->assertArrayHasKey('total', $response->json('meta'));
        // dd($response->json());
    }

    public function testStore()
    {
        $this->assertTrue(true);
    }

    public function testIndex()
    {
        $this->assertTrue(true);
    }

    public function testDestroy()
    {
        $this->assertTrue(true);
    }

    public function testUpdate()
    {
        $this->assertTrue(true);
    }
}
