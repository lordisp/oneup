<?php

namespace Tests\Feature\Services\DnsSync;


use App\Facades\DnsSync;
use App\Models\DnsSyncZone;
use Database\Seeders\DnsSyncZoneSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helper;
use Tests\TestCase;

class DnsSyncTest extends TestCase
{
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TokenCacheProviderSeeder::class);
        $this->seed(DnsSyncZoneSeeder::class);
    }

    /** @test */
    public function can_load_zones_into_an_array()
    {
        $this->assertDatabaseCount(DnsSyncZone::class, 1);

        $zones = DnsSyncZone::all()->toArray();

        $this->assertCount(1, $zones);
    }

    /** @test */
    public function mock_dns_sync_facade_with_public_methods()
    {
        DnsSync::shouldReceive('withRecordType')
            ->once()
            ->with(['A', 'AAAA'])
            ->andReturnSelf();

        DnsSync::shouldReceive('withHub')
            ->once()
            ->andReturnSelf();

        DnsSync::shouldReceive('withSpoke')
            ->once()
            ->with('azure')
            ->andReturnSelf();

        DnsSync::shouldReceive('start')->once();

        $sub = config('dnssync.subscription_id');
        $rg = config('dnssync.resource_group');

        $status = DnsSync::withRecordType(['A', 'AAAA'])
            ->withHub('azure', $sub, $rg)
            ->withSpoke('azure')
            ->start();

        $this->assertEquals(0, $status);
    }

    /** @test */
    public function run_dns_sync_in_one_tenant()
    {
        $sub = config('dnssync.subscription_id');
        $rg = config('dnssync.resource_group');

        $status = DnsSync::withRecordType(['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT'])
            ->withHub('lhg_arm', $sub, $rg)
            ->withSpoke('lhg_arm')
            ->start();
        $this->assertEquals(204, $status);
    }

    /** @test */
    public function run_dns_syn_on_hub_and_spoke_tenants()
    {
        $sub = config('dnssync.subscription_id');
        $rg = config('dnssync.resource_group');

        $status = DnsSync::withRecordType(['A'])
            ->withHub('lhg_arm', $sub, $rg)
            ->withSpoke('lhtest_arm')
            //->withSpoke('aviatar_arm')
            ->start();
        $this->assertEquals(204, $status);
    }

    /**
     * Testing helper method to get array of 53 private-dns zone names
     * @return array
     */
    protected function zones(): array
    {
        return array_map('trim', file(__DIR__ . '/stups/dns_zones.stup'));
    }
}
