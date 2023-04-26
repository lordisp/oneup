<?php

namespace Tests\Feature\Services\DnsSync;

use App\Exceptions\DnsZonesException;
use App\Facades\Pdns;
use Database\Seeders\DnsSyncZoneSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdnsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DnsSyncZoneSeeder::class,
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

        Pdns::withHub('lhg_arm', $subscriptionId, $resourceGroup)
            ->withZones(['privatelink.redis.cache.windows.net'])
            ->withRecordType('A')
            ->withSpoke('lhg_arm')
            ->withSubscriptions(['636529f0-5874-4a7f-9641-054746c3e250'])
            //->skipSubscriptions(['a32d7a5e-936c-495e-9d4f-a16d2469cc45'])
            ->sync();
        $this->assertTrue(true);
    }
}
