<?php

namespace Tests\Feature\Services;

use App\Facades\TokenCache;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Helper;
use Tests\TestCase;

class TokenCacheTest extends TestCase
{
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function can_acquire_an_access_token()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/provider_lhg_arm_token_response.json'), true)),
        ]);
        $token = TokenCache::provider('lhg_arm')->get();
        $this->assertIsString($token);
        // the $token is encrypted and must be decrypted to decode it
        $this->assertIsArray($this->decode(decrypt($token)));
    }

    /** @test */
    public function can_disable_token_encryption()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/provider_lhg_arm_token_response.json'), true)),
        ]);

        $token = TokenCache::provider('lhg_arm')->withoutEncryption()->get();
        $token = TokenCache::jwt($token);

        $this->assertArrayHasKey('aud', $token);
        $this->assertArrayHasKey('appid', $token);
    }

    /** @test */
    public function can_reuse_a_token()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/provider_lhg_arm_token_response.json'), true)),
        ]);
        $first = TokenCache::provider('lhg_arm')->get();
        $second = TokenCache::provider('lhg_arm')->get();
        $this->assertEquals($first, $second);
    }

    /** @test */
    public function can_acquire_an_azure_arm_token()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(__DIR__.'/stubs/provider_lhg_arm_token_response.json'), true)),
        ]);
        $instance = TokenCache::provider('lhg_arm');
        $this->assertEquals('lhg_arm', $this->accessProtected($instance, 'provider'));
        $this->assertIsString($instance->get());
    }

    /** @test */
    public function can_acquire_token_from_different_providers()
    {
        Http::fake([
            'https://login.microsoftonline.com/*' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/provider_lhg_arm_token_response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__.'/stubs/provider_lhgtest_arm_token_response.json'), true)),
        ]);
        $first = TokenCache::provider('lhtest_arm')->get();
        $second = TokenCache::provider('lhg_graph')->get();
        $jwt1 = TokenCache::jwt(decrypt($first));
        $jwt2 = TokenCache::jwt(decrypt($second));
        $this->assertNotEquals(data_get($jwt1, 'tid'), data_get($jwt2, 'tid'));
    }

    protected function decode($token)
    {
        return TokenCache::jwt($token);
    }
}
