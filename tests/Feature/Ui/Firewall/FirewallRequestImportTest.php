<?php

namespace Tests\Feature\Ui\Firewall;

use App\Events\FirewallReviewAvailableEvent;
use App\Events\ImportNewFirewallRequestsEvent;
use App\Events\NotifyFirewallImportCompletedEvent;
use App\Http\Livewire\PCI\FirewallRequestsImport;
use App\Jobs\ServiceNow\CleanUpFirewallRuleJob;
use App\Jobs\ServiceNow\ImportFirewallRequestJob;
use App\Models\Audit;
use App\Models\BusinessService;
use App\Models\FirewallRule;
use App\Models\ServiceNowRequest;
use App\Models\Subnet;
use App\Models\User;
use App\Notifications\FirewallRequestsImportedNotification;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SubnetSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;

class FirewallRequestImportTest extends TestCase implements FrontendTest
{
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserAzureSeeder::class,
            OperationSeeder::class,
            RoleSeeder::class,
            TokenCacheProviderSeeder::class,
        ]);
        User::first()->unassignRole('Global Administrator');
    }

    /** @test */
    public function cannot_access_route_as_guest(): void
    {
        $this->get('/firewall/requests/import')->assertRedirect('/login');
    }

    /** @test */
    public function dont_see_firewall_management_menus(): void
    {
        $user = User::first();
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertDontSee('Firewall Management')
            ->assertDontSee('View Requests')
            ->assertDontSee('Import Requests');
    }

    /** @test */
    public function can_see_firewall_reader_management_menus(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Firewall Management')
            ->assertSee('View Requests')
            ->assertDontSee('Import Requests');
    }

    /** @test */
    public function can_see_firewall_admin_management_menus(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Firewall Management')
            ->assertSee('View Requests')
            ->assertSee('Import Requests');
    }

    /** @test */
    public function can_access_route_as_user(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        $this->actingAs($user)
            ->get('/firewall/requests/import')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->assertOk()
            ->assertViewIs('livewire.p-c-i.firewall-requests-import');
    }

    /** @test */
    public function can_view_component(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        $this->actingAs($user)
            ->get('/firewall/requests/import')
            ->assertSeeLivewire('p-c-i.firewall-requests-import')
            ->assertSee('Browse files')
            ->assertSee('import')
            ->assertSee('Import Firewall Requests');
    }

    /** @test */
    public function invalid_file_structure_flashes_error_message(): void
    {
        Storage::fake('tmp-for-tests');
        $first = file_get_contents(base_path().'/tests/Feature/Stubs/firewallImport/invalid_1.json');
        $files[] = UploadedFile::fake()->createWithContent('invalid_1.json', $first);

        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->set('attachments', $files)
            ->assertHasErrors(['attachments.0']);
    }

    /** @test */
    public function test_import_job(): void
    {
        // Arrange
        Subnet::factory()->createMany([
            ['name' => '10.123.207.0', 'size' => 24],
            ['name' => '10.123.186.0', 'size' => 24],
            ['name' => '10.123.75.0', 'size' => 24],
        ]);
        $fileContents = $this->getStub('firewallImport/valid.json');

        // Assert
        $this->assertDatabaseCount(ServiceNowRequest::class, 0);
        $this->assertDatabaseCount(FirewallRule::class, 0);
        BusinessService::all()->each->delete();
        User::all()->each->delete();

        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        // Act
        foreach ($fileContents as $fileContent) {
            ImportFirewallRequestJob::dispatch(User::factory()->create(), $fileContent);
        }

        // Assert
        $this->assertDatabaseCount(ServiceNowRequest::class, 3);
        $this->assertDatabaseCount(User::class, 4);
        $this->assertDatabaseCount(BusinessService::class, 2);
        $this->assertDatabaseCount(FirewallRule::class, 5);
        $this->assertDatabaseCount(Audit::class, 7);
        $this->assertCount(
            3,
            FirewallRule::query()
                ->review()
                ->get()
        );
    }

    /** @test */
    public function can_import_a_valid_json_file(): void
    {
        Subnet::factory()->createMany([
            ['name' => '10.253.207.0', 'size' => 24],
            ['name' => '10.253.186.0', 'size' => 24],
            ['name' => '10.253.75.0', 'size' => 24],
        ]);

        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        $this->seed(SubnetSeeder::class);
        $this->assertDatabaseCount(BusinessService::class, 0);
        $this->assertDatabaseCount(Subnet::class, 28);
        $this->assertDatabaseCount(Audit::class, 0);

        $this->importOneFile();

        $this->assertDatabaseCount(ServiceNowRequest::class, 3);
        $this->assertDatabaseCount(FirewallRule::class, 5);
        $this->assertDatabaseCount(BusinessService::class, 2);
        $this->assertDatabaseCount(Audit::class, 6);
    }

    /** @test */
    public function can_import_a_valid_json_file_twice(): void
    {
        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        Subnet::factory()->createMany([
            ['name' => '10.123.207.0', 'size' => 24],
            ['name' => '10.123.186.0', 'size' => 24],
            ['name' => '10.123.75.0', 'size' => 24],
        ]);

        $this->importOneFile();

        $pciRules = FirewallRule::query()->review()->get();
        $this->assertCount(2, $pciRules);
        $this->seed(SubnetSeeder::class);

        $this->importOneFile();

        $this->assertDatabaseCount(ServiceNowRequest::class, 3);

        $this->assertDatabaseCount(FirewallRule::class, 5);
        $pciRules = FirewallRule::query()->review()->get();
        $this->assertCount(2, $pciRules);
    }

    /** @test */
    public function import_firewall_rules_expect_queued_import_jobs(): void
    {
        Queue::fake();

        $this->importOneFile();

        Queue::assertPushed(ImportFirewallRequestJob::class, 3);
    }

    /** @test */
    public function import_firewall_rules_expect_queued_import_event(): void
    {
        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        Event::fake();

        $this->importOneFile();

        Event::assertDispatched(ImportNewFirewallRequestsEvent::class, 1);
    }

    /** @test */
    public function expect_available_firewall_review_available_events(): void
    {
        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        Event::fake([FirewallReviewAvailableEvent::class]);

        $this->importOneFile();

        Event::assertDispatched(FirewallReviewAvailableEvent::class, 1);
    }

    /** @test */
    public function expect_processing_firewall_review_available_event(): void
    {
        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        Queue::fake([CleanUpFirewallRuleJob::class]);
        Event::fake([NotifyFirewallImportCompletedEvent::class]);

        $this->importOneFile();

        Queue::assertPushed(CleanUpFirewallRuleJob::class, 1);
        Event::assertDispatched(NotifyFirewallImportCompletedEvent::class, 1);

    }

    /** @test */
    public function expect_notification_to_admin_after_import(): void
    {
        Http::fake([
            config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true)),
            'https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true)),
            'https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true)),
        ]);

        Notification::fake();

        $this->importOneFile();

        Notification::assertSentTo([User::first()], FirewallRequestsImportedNotification::class, 1);
    }
}
