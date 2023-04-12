<?php

namespace Tests\Feature\Ui\Firewall;

use App\Events\ImportNewFirewallRequestsEvent;
use App\Http\Livewire\PCI\FirewallRequestsImport;
use App\Jobs\ServiceNow\ImportFirewallRequestJob;
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
use Illuminate\Support\Arr;
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
    use RefreshDatabase, Helper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserAzureSeeder::class,
            OperationSeeder::class,
            RoleSeeder::class,
            TokenCacheProviderSeeder::class
        ]);
        User::first()->unassignRole('Global Administrator');
    }

    /** @test */
    public function cannot_access_route_as_guest()
    {
        $this->get('/firewall/requests/import')->assertRedirect('/login');
    }


    /** @test */
    public function dont_see_firewall_management_menus()
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
    public function can_see_firewall_reader_management_menus()
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
    public function can_see_firewall_admin_management_menus()
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
    public function can_access_route_as_user()
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        $this->actingAs($user)
            ->get('/firewall/requests/import')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component()
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->assertOk()
            ->assertViewIs('livewire.p-c-i.firewall-requests-import');
    }

    /** @test */
    public function can_view_component()
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
    public function invalid_file_structure_flashes_error_message()
    {
        Storage::fake('tmp-for-tests');
        $first = file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/invalid_1.json');
        $files[] = UploadedFile::fake()->createWithContent('invalid_1.json', $first);

        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->set('attachments', $files)
            ->assertHasErrors(['attachments.0']);
    }


    /** @test */
    public function can_retrieve_responsibles_from_business_service_over_service_now_api()
    {
        // Arrange
        $clientId = config('servicenow.client_id');
        $secret = config('servicenow.client_secret');
        $url = config('servicenow.uri') . '/api/delag/retrieve_cost_centers/GetGroupFromBsandType';

        // Act
        $response = Http::withBasicAuth($clientId, $secret)
            ->retry(3)
            ->post($url, [
                'types' => ['Responsibles', 'EscalationNotification', 'SecurityContacts'],
                'names' => ['LHG_GAC_P']
            ]);

        // Assert
        $this->assertEquals(200, $response->status());
        $this->assertContains(
            'Rafael.Camison@austrian.com',
            Arr::flatten($response->json('result'))
        );
    }

    /** @test */
    public function test_import_job()
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
        BusinessService::truncate();
        User::truncate();

        Http::fake([config('servicenow.uri') . '/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);

        // Act
        foreach ($fileContents as $fileContent) {
            ImportFirewallRequestJob::dispatch(User::factory()->create(), $fileContent);
        }

        // Assert
        $this->assertDatabaseCount(ServiceNowRequest::class, 3);
        $this->assertDatabaseCount(User::class, 4);
        $this->assertDatabaseCount(BusinessService::class, 2);
        $this->assertDatabaseCount(FirewallRule::class, 5);
        $this->assertCount(
            3,
            FirewallRule::query()
                ->review()
                ->get()
        );
    }

    /** @test */
    public function can_import_a_valid_json_file()
    {
        Subnet::factory()->createMany([
            ['name' => '10.253.207.0', 'size' => 24],
            ['name' => '10.253.186.0', 'size' => 24],
            ['name' => '10.253.75.0', 'size' => 24],
        ]);

        Http::fake([config('servicenow.uri') . '/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);

        $this->seed(SubnetSeeder::class);
        $this->assertDatabaseCount(BusinessService::class, 0);
        $this->assertDatabaseCount(Subnet::class, 28);
        $this->importOneFile();

        $this->assertDatabaseCount(ServiceNowRequest::class, 3);

        $this->assertDatabaseCount(FirewallRule::class, 5);
        $this->assertDatabaseCount(BusinessService::class, 2);
    }

    /** @test */
    public function can_import_a_valid_json_file_twice()
    {
        Http::fake([config('servicenow.uri') . '/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);

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
    public function import_firewall_rules_expect_queued_import_jobs()
    {
        Queue::fake();

        $this->importOneFile();

        Queue::assertPushed(ImportFirewallRequestJob::class, 3);
    }

    /** @test */
    public function import_firewall_rules_expect_queued_import_event()
    {
        Event::fake();

        $this->importOneFile();

        Event::assertDispatched(ImportNewFirewallRequestsEvent::class, 1);
    }

    /** @test */
    public function expect_notification_to_admin_after_import()
    {
        Notification::fake();

        Http::fake([config('servicenow.uri') . '/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);
        $this->importOneFile();

        Notification::assertSentTo([User::first()], FirewallRequestsImportedNotification::class, 1);

    }

    protected function importOneFile(string $file = '')
    {
        $file = !empty($file) ? $file : 'valid.json';
        Storage::fake('tmp-for-tests');
        $first = file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/' . $file);
        $files[] = UploadedFile::fake()->createWithContent('file.json', $first);

        $user = User::first();
        $user->assignRole('Firewall Administrator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->set('attachments', $files)
            ->assertHasNoErrors()
            ->call('save');
    }
}
