<?php

namespace Tests\Feature\Services\AzureArm;

use App\Exceptions\AzureArm\ResourceGraphException;
use App\Facades\AzureArm\ResourceGraph;
use App\Facades\Redis;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResourceGraphTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TokenCacheProviderSeeder::class);
    }

    /** @test */
    public function an_invalid_subscription_id_throws_an_exception()
    {
        $this->expectException(ResourceGraphException::class);

        ResourceGraph::withSubscription('foo');
    }

    /** @test */
    public function a_resource_can_be_found_in_the_request()
    {
        $subscriptionId = '636529f0-5874-4a7f-9641-054746c3e250';

        $resources = ResourceGraph::withSubscription($subscriptionId)->get();

        $resourceName = 'vltlhgsmgixi01p';

        $resourceMatch = array_sum(
            array_map(fn($key) => Str::contains($key['name'], $resourceName), $resources)
        );

        $this->assertGreaterThan(0, $resourceMatch);

    }

    /** @test */
    public function can_cache_results_and_delete_the_cached_data()
    {
        $this->mockRedis();

        $cached = ResourceGraph::type('microsoft.network/networkinterfaces')
            ->extend('key', 'id')
            ->extend('value', 'tostring(properties.ipConfigurations)')
            ->project('key', 'value')
            ->toCache('someResources', 600);

        $this->assertGreaterThan(100, array_sum($cached));

        $withoutKeys = ResourceGraph::fromCache('someResources');
        $withKeys = ResourceGraph::fromCache('someResources', true);

        $this->assertSameSize($withoutKeys, $withKeys);

        $deleted = ResourceGraph::deleteCache('someResources');

        $this->assertTrue($deleted);

        $someResources = ResourceGraph::fromCache('someResources');

        $this->assertCount(0, $someResources);
    }

    /** @test */
    public function set_values_with_extend()
    {
        $results = ResourceGraph::type('microsoft.network/networkinterfaces')
            ->extend('ipConfigurationsString', 'tostring(properties.ipConfigurations)')
            ->extend('ipConfigurations', 'properties.ipConfigurations')
            ->where('ipConfigurationsString', 'has', '10.253.87.75')
            ->project('name, ipConfigurations')
            ->get();

        $this->assertCount(1, $results);
    }

//    /** @test */
    public function dddd()
    {
        $foo = Redis::hDel('myhash', 'myfield', 'myfield2');
//        $foo[] = Redis::hSet('myhash', 'myfield', 'myvalue');
//        $foo[] = Redis::hSet('myhash', 'myfield2', 'myvalue');

        dd($foo);
    }

    private function mockRedis()
    {
        for ($i = 0; $i < 10; $i++) {
            $array[$i] = rand();
        }

        Redis::shouldReceive('hSet')->andReturn(1)
            ->atLeast()->times(2000)
            ->atMost()->times(5000);
        Redis::shouldReceive('hVals')->andReturn($array)->once();
        Redis::shouldReceive('hGetAll')->andReturn($array)->once();
        Redis::shouldReceive('hVals')->andReturn([])->once();
        Redis::shouldReceive('hKeys')->andReturn($array)->once();
        Redis::shouldReceive('hDel')->andReturn(1)->times(10);
        Redis::shouldReceive('expire')->andReturn(1)->once();
    }
}
