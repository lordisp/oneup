<?php

namespace Tests\Feature\Services\AzureArm;

use App\Exceptions\AzureArm\ResourceGraphException;
use App\Facades\AzureArm\ResourceGraph;
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
    public function a_resource_can_be_found_in_the_cache()
    {
        $subscriptionId = '636529f0-5874-4a7f-9641-054746c3e250';

        ResourceGraph::withSubscription($subscriptionId)->cache();

        $resourceName = 'vltlhgsmgixi01p';

        $resources = ResourceGraph::fromCache();

        $resourceMatch = array_sum(
            array_map(fn($key) => Str::contains($key, $resourceName), $resources)
        );

        $this->assertGreaterThan(0, $resourceMatch);

    }

    /** @test */
    public function it_cache_more_than_1000_but_less_than_15000_resources()
    {
        ResourceGraph::type('microsoft.network/networkinterfaces')
            ->extend('name', 'tostring(properties.ipConfigurations)')
            ->project('name')
            ->cache();

        $cached = ResourceGraph::fromCache();

        $this->assertLessThan(15000, count($cached));

        $this->assertGreaterThan(1000, count($cached));
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

}
