<?php

namespace Tests\Feature\Services\DnsSync;

use App\Events\PdnsSyncEvent;
use App\Events\StartNewPdnsSynchronization;
use App\Jobs\PdnsSync;
use App\Listeners\RequestNetworkInterfaces;
use App\Models\TokenCacheProvider;
use Database\Seeders\DnsSyncAllZonesSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
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
    public function event_throws_exception_with_invalid_data()
    {
        $this->expectException(ValidationException::class);

        event(new StartNewPdnsSynchronization(['foo']));
    }

    /** @test */
    public function a_start_new_pdns_synchronization_event_will_be_triggered()
    {
        Event::fake();

        event(new StartNewPdnsSynchronization([
            'hub' => 'lhg_arm',
            'spoke' => 'lhg_arm',
            'recordType' => ['A'],
        ]));

        Event::assertDispatched(StartNewPdnsSynchronization::class, 1);

        Event::assertListening(StartNewPdnsSynchronization::class, RequestNetworkInterfaces::class);

    }

    /** @test */
    public function run_a_full_private_dns_synchronization_on_one_subscription()
    {
        event(new StartNewPdnsSynchronization([
            'hub' => 'lhg_arm',
            'spoke' => 'lhg_arm',
            'withSubscriptions' => [
                '636529f0-5874-4a7f-9641-054746c3e250',
            ],
            'recordType' => ['A'],
            'skipZonesForValidation' => [
                'privatelink.postgres.database.azure.com',
                'privatelink.api.azureml.ms',
            ],
        ]));

        $this->assertTrue(true);
    }

    /** @test */
    public function sync_batch_triggers_two_sync_events()
    {
        TokenCacheProvider::factory()->state([
            'name' => 'aviatar_arm',
            'auth_endpoint' => 'foo',
            'client' => 'bar',
        ])->create();

        Event::fake();

        PdnsSync::dispatch();

        Event::assertDispatched(StartNewPdnsSynchronization::class, 2);


    }
}
