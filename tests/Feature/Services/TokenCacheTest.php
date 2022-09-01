<?php

namespace Tests\Feature\Services;

use App\Facades\TokenCache;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper;
use Tests\TestCase;

class TokenCacheTest extends TestCase
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function can_acquire_an_access_token()
    {
        $token = TokenCache::provider('lhg_arm')->get();
        $this->assertIsString($token);
        // the $token is encrypted and must be decrypted to decode it
        $this->assertIsObject($this->decode(decrypt($token)));
    }

    /** @test */
    public function can_disable_token_encryption()
    {
        $token1= TokenCache::provider('lhg_arm')->get();
        $token2= TokenCache::provider('lhg_arm')->withoutEncryption()->get();
        $this->assertEquals(decrypt($token1),$token2);
    }

    /** @test */
    public function can_reuse_a_token()
    {
        $first = TokenCache::provider('lhg_arm')->get();
        $second = TokenCache::provider('lhg_arm')->get();
        $this->assertEquals($first, $second);
    }

    /** @test */
    public function can_acquire_an_azure_arm_token()
    {
        $instance = TokenCache::provider('lhg_arm');
        $this->assertEquals('lhg_arm', $this->accessProtected($instance, 'provider'));
        $this->assertIsString($instance->get());
    }

    protected function decode($token)
    {
        return TokenCache::jwt($token);
    }
}
