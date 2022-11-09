<?php

namespace Tests\Feature\Services\DnsSync;


use App\Facades\DnsSync;
use App\Models\DnsSyncZone;
use Database\Seeders\DnsSyncZoneSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        Http::fake($this->fakeDnsResponses());

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
        Http::fake($this->fakeDnsResponses());
        $sub = config('dnssync.subscription_id');
        $rg = config('dnssync.resource_group');

        $status = DnsSync::withRecordType(['A'])
            ->withHub('lhg_arm', $sub, $rg)
            ->withSpoke('lhtest_arm')
            ->start();
        $this->assertEquals(204, $status);
    }

    protected function fakeDnsResponses(): array
    {
        return [
            'https://login.microsoftonline.com/*/oauth2/*' => Http::response(json_decode(file_get_contents(__DIR__ . './../stubs/provider_lhg_arm_token_response.json'), true)),
            'https://management.azure.com/providers/Microsoft.ResourceGraph/*' => Http::sequence()
                ->push(status: 401)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/zone_response.json'), true)),

            'https://management.azure.com/subscriptions/*/ALL?api-version=2018-09-01&$top=1000' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-1-response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-2-response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-3-response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-4-response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-5-response.json'), true))
                ->push(status: 401)
                ->push(status: 408)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-6-response.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/records/record-7-response.json'), true))
            ,

            // Etag
            'https://management.azure.com/subscriptions/18d6c26e-6e4c-4d49-9849-e8d15fb21b08/resourceGroups/rg_lhg_ams_pldnszones_p/providers/Microsoft.Network/privateDnsZones/*api-version=2018-09-01' => Http::sequence()
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-1.json'), true))
                ->push(['code' => ''], status: 408)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/put/response-7.json'), true))
                ->push(['code' => ''], status: 408)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-2.json'), true))
                ->push(status: 401)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-3.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-4.json'), true))
                ->push(status: 401)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-5.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-6.json'), true))
                ->push(['code' => ''], status: 404)
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-7.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-8.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-9.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-10.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-11.json'), true))
                ->push(json_decode(file_get_contents(__DIR__ . '/stups/etags/response-12.json'), true))
        ];
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
