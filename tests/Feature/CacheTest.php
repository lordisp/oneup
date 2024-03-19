<?php

namespace Tests\Feature;

use App\Facades\Redis;
use Psr\SimpleCache\InvalidArgumentException;
use Tests\TestCase;

class CacheTest extends TestCase
{
    /** @test
     * @throws InvalidArgumentException
     */
    public function can_rwd_cache(): void
    {
        cache()->add('name', 'Rafael');

        $this->assertEquals('Rafael', cache('name'));

        cache()->delete('name');

        $this->assertEquals(null, cache('name'));
    }

    /** @test */
    public function can_add_key_to_redis_db(): void
    {
        Redis::shouldReceive('set')->andReturn(true);

        $this->assertTrue(Redis::set('name', 'Rafael'));
        $this->assertTrue(Redis::set('last', 'Camison'));
    }

    /** @test */
    public function can_read_key_from_redis_db(): void
    {
        Redis::shouldReceive('get')->andReturn('Rafael');

        $this->assertEquals('Rafael', Redis::get('name'));
    }

    /** @test */
    public function can_delete_key_from_redis_db(): void
    {
        Redis::shouldReceive('del')->once()->andReturn('1');
        Redis::shouldReceive('get')->once()->andReturn(null);

        $this->assertEquals('1', Redis::del('name'));
        $this->assertEquals(null, Redis::get('name'));
    }
}
