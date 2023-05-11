<?php

namespace Tests\Feature\Services\DnsSync;

use App\Exceptions\DnsZonesException;
use App\Exceptions\UpdateRecordJobException;
use App\Facades\AzureArm\ResourceGraph;
use App\Facades\Pdns;
use App\Jobs\Pdns\PdnsQueryZoneRecordsJob;
use App\Jobs\Pdns\UpdateRecordJob;
use Database\Seeders\DnsSyncAllZonesSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
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

    /** @test
     * @throws DnsZonesException
     */
    public function can_sync_with_hub_with_spoke_with_record_type()
    {
        $subscriptionId = config('dnssync.subscription_id');
        $resourceGroup = config('dnssync.resource_group');
        $this->cacheResources();

        Pdns::withHub('lhg_arm', $subscriptionId, $resourceGroup)
            ->withZones(['privatelink.redis.cache.windows.net'])
            ->withRecordType('A')
            ->withSpoke('lhg_arm')
            ->withSubscriptions(['636529f0-5874-4a7f-9641-054746c3e250'])
            ->sync();
        $this->assertTrue(true);
    }

    /** @test */
    public function it_gets_all_zones()
    {
        Queue::fake();

        $subscriptionId = config('dnssync.subscription_id');
        $resourceGroup = config('dnssync.resource_group');

        Pdns::withHub('lhg_arm', $subscriptionId, $resourceGroup)
            ->withZones(['privatelink.redis.cache.windows.net'])
            ->withRecordType('A')
            ->withSpoke('lhg_arm')
            ->withSubscriptions(['636529f0-5874-4a7f-9641-054746c3e250'])
            ->sync();


        Queue::assertPushed(PdnsQueryZoneRecordsJob::class, 1);
    }

    /** @test */
    public function can_call_fluent_interfaces()
    {
        $subscriptionId = config('dnssync.subscription_id');
        $resourceGroup = config('dnssync.resource_group');

        Pdns::shouldReceive('sync')
            ->andReturnSelf();
        Pdns::shouldReceive('withHub')
            ->with('lhg_arm', $subscriptionId, $resourceGroup)
            ->andReturnSelf();
        Pdns::shouldReceive('withZones')
            ->with(['privatelink.redis.cache.windows.net'])
            ->andReturnSelf();
        Pdns::shouldReceive('withRecordType')
            ->with('A')
            ->andReturnSelf();
        Pdns::shouldReceive('withSpoke')
            ->with('lhg_arm')
            ->andReturnSelf();
        Pdns::shouldReceive('withSubscriptions')
            ->with(['636529f0-5874-4a7f-9641-054746c3e250'])
            ->andReturnSelf();

        $this->runSingleTenantSync();

        $this->assertTrue(true);
    }

    /** @test */
    public function it_throws_an_dns_zones_exception_after_100_failed_zone_requests()
    {
        $this->expectException(DnsZonesException::class);

        $responses = array_fill(0, 99, Http::response(status: 429));
        $responses[] = Http::response(status: 500);

        Http::fake($this->getFakeToken());
        Http::fake(['https://management.azure.com/*' => Http::sequence($responses)]);

        Pdns::sync();
    }

    /** @test */
    public function it_queues_as_much_pdns_query_zone_records_jobs_as_zones()
    {
        Queue::fake();

        Http::fake($this->getFakeToken());

        Http::fake(['https://management.azure.com/*' => Http::response(
            json_decode(file_get_contents('./tests/Feature/Stubs/pdns/two_zones.json'), true)
        )]);

        $this->runSingleTenantSync();

        Queue::assertPushed(PdnsQueryZoneRecordsJob::class, 1);

    }

    /** @test */
    public function it_queues_as_much_pdns_update_record_jobs_as_records_found()
    {
        Queue::fake(UpdateRecordJob::class);

        Http::fake($this->getFakeToken());

        Http::fake(['https://management.azure.com/providers/Microsoft.ResourceGraph/resources*' => Http::sequence()
            ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/two_zones.json'), true))
        ]);

        Http::fake([
            'https://management.azure.com/*/ALL?api-version=2018-09-01&$top=1000' => Http::sequence()
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/redis_cache_records.json'), true))
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/blob_core_records.json'), true))
        ]);

        $this->runSingleTenantSync();

        Queue::assertPushed(UpdateRecordJob::class, 3);


    }

    /** @test */
    public function it_caches_resources_by_record_name()
    {
        Queue::fake(UpdateRecordJob::class);

        Http::fake($this->getFakeToken());

        Http::fake(['https://management.azure.com/providers/Microsoft.ResourceGraph/resources*' => Http::sequence()
            ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/two_zones.json'), true))
            ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/record_resources.json'), true))
        ]);

        Http::fake([
            'https://management.azure.com/*/ALL?api-version=2018-09-01&$top=1000' => Http::sequence()
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/redis_cache_records.json'), true))
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/blob_core_records.json'), true))
        ]);

        Http::fake([
            'https://management.azure.com/subscriptions/18d6c26e-6e4c-4d49-9849-e8d15fb21b08/resourceGroups/rg_lhg_ams_pldnszones_p/providers/Microsoft.Network/privateDnsZones/privatelink.redis.cache.windows.net/A/*?api-version=2018-09-01' => Http::sequence()
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/etag-acfr-lhg-giti-p.json'), true))
                ->push(status: 201)
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/etag-acfr-lhg-giti-p.json'), true))
                ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/etag-acfr-lhg-giti-p.json'), true))
            //->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/blob_core_records.json'), true))
        ]);

        $this->runSingleTenantSync();

        Queue::assertPushed(UpdateRecordJob::class, 3);

    }

    /** @test */
    public function can_run_a_full_sync()
    {
        $this->cacheResources();

        $this->runSingleTenantSync();

        $this->assertTrue(true);
    }

    /** @test */
    public function an_exception_will_be_thrown_if_cache_is_empty()
    {
        Http::fake($this->getFakeToken());
        Http::fake(['https://management.azure.com/*' => Http::sequence()
            ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/two_zones.json'), true))
            ->push(json_decode(file_get_contents('./tests/Feature/Stubs/pdns/redis_cache_records.json'), true))
        ]);

        $this->expectException(UpdateRecordJobException::class);

        $this->runSingleTenantSync();
    }

    protected function runSingleTenantSync()
    {
        $subscriptionId = config('dnssync.subscription_id');
        $resourceGroup = config('dnssync.resource_group');

        Pdns::withHub('lhg_arm', $subscriptionId, $resourceGroup)
            ->withZones(['privatelink.redis.cache.windows.net'])
            ->withRecordType('A')
            ->withSpoke('lhg_arm')
            ->withSubscriptions(['636529f0-5874-4a7f-9641-054746c3e250'])
            ->sync();
    }

    protected function cacheResources($provider = 'lhg_arm')
    {
        ResourceGraph::withProvider($provider)
            ->type('microsoft.network/networkinterfaces')
            ->extend('name', 'tostring(properties.ipConfigurations)')
            ->project('name')
            ->cache();
    }
}
