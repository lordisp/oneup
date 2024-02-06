<?php

namespace Tests\Feature\Services\DnsSync;

use App\Events\InterfacesReceived;
use App\Jobs\Pdns\PrivateDnsSync;
use App\Jobs\Pdns\ProcessPrivateDnsSyncJob;
use App\Jobs\Pdns\QueryZoneRecords;
use App\Jobs\RequestNetworkInterfacesJob;
use Database\Seeders\DnsSyncAllZonesSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
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

    /** @test */
    public function it_should_dispatch_request_network_interfaces_job_when_private_dns_sync_is_run()
    {
        //Arrange
        app()->get('config')->set('dnssync.provider', 'lhtest_arm');
        Bus::fake([RequestNetworkInterfacesJob::class]);

        //Act
        PrivateDnsSync::dispatch();

        //Assert
        Bus::assertBatched(function (PendingBatch $batch) {
            return $batch->name == 'pdns' &&
                $batch->jobs->count() === 1 &&
                $batch->jobs->filter(function ($job) {
                    return $job instanceof RequestNetworkInterfacesJob;
                })->count() === $batch->jobs->count();
        });
    }

    /** @test */
    public function private_dns_sync_dispatch_should_trigger_interfaces_received_event_when_data_is_cached()
    {
        //Arrange
        app()->get('config')->set('dnssync.provider', 'lhtest_arm');
        Event::fake([InterfacesReceived::class]);

        //Act
        PrivateDnsSync::dispatch();

        //Assert
        Event::assertDispatched(InterfacesReceived::class);
    }


    /** @test */
    public function it_dispatches_private_dns_sync_correctly()
    {
        app()->get('config')->set('dnssync.provider', 'lhtest_arm');

        Queue::fake([ProcessPrivateDnsSyncJob::class]);
        //Act
        PrivateDnsSync::dispatch();

        Queue::assertPushed(ProcessPrivateDnsSyncJob::class, 1);
        $this->assertTrue(true);
    }

    /** @test */
    public function query_zone_records_is_queued_when_private_dns_sync_is_dispatched()
    {
        app()->get('config')->set('dnssync.provider', 'lhtest_arm');
        Queue::fake([QueryZoneRecords::class]);

        PrivateDnsSync::dispatch();

        Queue::assertPushed(QueryZoneRecords::class);
    }
}