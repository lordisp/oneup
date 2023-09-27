<?php

namespace Tests\Feature\Models;

use App\Models\TokenCacheProvider;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenCacheProviderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_a_provider()
    {
        $provider = TokenCacheProvider::factory()->create();
        $this->assertDatabaseCount(TokenCacheProvider::class, 1);
        $this->assertArrayHasKey('name', $provider->toArray());
        $this->assertArrayHasKey('auth_url', $provider->toArray());
        $this->assertArrayHasKey('token_url', $provider->toArray());
        $this->assertArrayHasKey('auth_endpoint', $provider->toArray());
        $this->assertArrayHasKey('client', $provider->toArray());
        $this->assertArrayHasKey('id', $provider->toArray());
        $this->assertArrayHasKey('updated_at', $provider->toArray());
        $this->assertArrayHasKey('created_at', $provider->toArray());
    }

    /** @test */
    public function can_retrieve_a_single_provider()
    {
        $this->seed(TokenCacheProviderSeeder::class);
        $provider = TokenCacheProvider::first();
        $this->assertIsObject($provider);
    }

    /** @test */
    public function can_retrieve_all_providers()
    {
        $this->seed(TokenCacheProviderSeeder::class);
        $provider = TokenCacheProvider::all();
        $this->assertIsObject($provider);
        $this->assertCount(5, $provider);
    }
}
