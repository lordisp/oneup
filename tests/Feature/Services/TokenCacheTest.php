<?php

namespace Tests\Feature\Services;

use App\Facades\TokenCache;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

class TokenCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function can_acquire_an_access_token()
    {
        $token = TokenCache::get();
        $this->assertIsString($token);
        // the $token is encrypted and must be decrypted to decode it
        $this->assertIsObject($this->decode(decrypt($token)));
    }

    /** @test */
    public function can_disable_token_encryption()
    {
        $instance = TokenCache::withoutEncryption();
        $this->assertFalse($this->accessProtected($instance, 'config')['encrypt']);
        // the $token is NOT encrypted and must be decrypted to decode it
        $token =$instance->get();
        $this->assertIsString($token);
        $this->assertIsObject($this->decode($token));
    }

    /** @test */
    public function can_reuse_a_token()
    {
        // 'azure_ad' is the default provider. The second request should
        $first = TokenCache::provider('azure')->get();
        $second = TokenCache::provider('azure')->get();
        $this->assertEquals($first, $second);
    }

    /** @test */
    public function can_acquire_an_azure_arm_token()
    {
        $instance = TokenCache::provider('azure');
        $this->assertEquals('azure', $this->accessProtected($instance, 'provider'));
        $this->assertIsString($instance->get());
    }

    protected function accessProtected($obj, $prop)
    {
        $property = (new ReflectionClass($obj))->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    protected function setProtected($objectOrClass, $property, $object, $value)
    {
        $reflectionClass = new ReflectionClass($objectOrClass);
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
        return $object;
    }

    protected function decode($token)
    {
        return TokenCache::jwt($token);
    }
}
