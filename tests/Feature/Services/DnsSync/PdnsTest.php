<?php

namespace Tests\Feature\Services\DnsSync;

use App\Facades\AzureArm\ResourceGraph;
use App\Facades\Redis;
use App\Jobs\Pdns\LhgTenantJob;
use App\Jobs\Pdns\PdnsQueryZoneRecordsJob;
use App\Jobs\PdnsSync;
use App\Jobs\RequestNetworkInterfacesJob;
use App\Services\Pdns\Pdns;
use Database\Seeders\DnsSyncAllZonesSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\Helper;
use Tests\TestCase;

class PdnsTest extends TestCase
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DnsSyncAllZonesSeeder::class,
            TokenCacheProviderSeeder::class
        ]);
    }

    /** @test */
    public function it_executes_batch_jobs_in_the_correct_order()
    {
        Bus::fake()->except(PdnsSync::class);

        PdnsSync::dispatch();

        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->jobs->flatten()->count() > 1;
        });
    }

    /** @test
     * @depends  it_executes_batch_jobs_in_the_correct_order
     */
    public function can_request_network_interfaces()
    {
        Redis::shouldReceive('hSet')
            ->andReturn(1)
            ->atLeast()
            ->times(100);
        Redis::shouldReceive('expire')
            ->andReturn(1)
            ->times(1);

        RequestNetworkInterfacesJob::dispatch('some_provider');
    }

    /** @test
     * @depends can_request_network_interfaces
     */
    public function the_hub_tenant_can_be_dispatched()
    {
        Queue::fake();

        $this->mock(Pdns::class, function (MockInterface $mock) {
            $mock->shouldReceive('withSpoke')->with('lhg_arm')->andReturnSelf();
            $mock->shouldReceive('withRecordType')->andReturnSelf();
            $mock->shouldReceive('skipSubscriptions')->andReturnSelf();
            $mock->shouldReceive('skipZonesForValidation')->andReturnSelf();
            $mock->shouldReceive('sync')->andReturnSelf();
        });

        $job = new LhgTenantJob();
        $job->handle();

        Queue::assertPushed(PdnsQueryZoneRecordsJob::class);
    }

    /** @test
     * @depends the_hub_tenant_can_be_dispatched
     */
    public function run_a_full_private_dns_sync_on_one_subscription()
    {
        Queue::fake(RequestNetworkInterfacesJob::class);

        $resources = Arr::flatten(
            ResourceGraph::withProvider('lhg_arm')
                ->type('microsoft.network/networkinterfaces')
                ->extend('value', 'tostring(properties.ipConfigurations)')
                ->project('value')
                ->get()
        );

        Redis::shouldReceive('hVals')
            ->andReturn($resources)
            ->atLeast()
            ->times(10);

        (new Pdns)
            ->withSpoke('lhg_arm')
            ->withRecordType(['A', 'CNAME'])
            ->withSubscriptions([
                '636529f0-5874-4a7f-9641-054746c3e250',
            ])
            ->skipZonesForValidation([
                'privatelink.postgres.database.azure.com',
                'privatelink.westeurope.azmk8s.io',
                'privatelink.api.azureml.ms',
            ])
            ->sync();

        Queue::assertPushed(RequestNetworkInterfacesJob::class, 1);
    }
}
