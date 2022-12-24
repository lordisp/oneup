<?php

namespace Tests\Feature\Ui\Firewall;

use App\Events\ImportNewFirewallRequestsEvent;
use App\Http\Livewire\PCI\FirewallRequestsImport;
use App\Jobs\ServiceNow\ImportServiceNowFirewallRequestsJob;
use App\Models\FirewallRule;
use App\Models\ServiceNowRequest;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\FirewallRequestsImportedNotification;
use App\Services\Scim;
use Database\Seeders\BusinessServiceSeeder;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
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
            BusinessServiceSeeder::class,
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
        $first = file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/invalid_1.json');
        $files[] = UploadedFile::fake()->createWithContent('invalid_1.json', $first);

        $user = User::first();
        $user->assignRole('Firewall-Requests Operator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->set('attachments', $files)
            ->assertHasErrors(['attachments.0']);
    }

    /** @test */
    public function test_import_job()
    {
        $files = array_merge(
            $this->getStub('firewallImport/valid_1.json')
        );
        $User = User::first();

        foreach ($files as $file) {
            ImportServiceNowFirewallRequestsJob::dispatch($User, $file);
        }

        $this->assertDatabaseCount(ServiceNowRequest::class, 4);
    }

    /** @test */
    public function can_import_a_valid_json_file()
    {
        $this->importOneFile();

        $rule = FirewallRule::where('pci_dss', true)
            ->first();
        $rule->status = 'extended';
        $rule->save();

        $this->importOneFile();

        $this->assertDatabaseCount(ServiceNowRequest::class, 4);

        $this->assertDatabaseCount(FirewallRule::class, 7);
    }

    /** @test */
    public function can_import_a_valid_json_file_twice()
    {
        $this->importOneFile();

        $this->importOneFile();

        $this->assertDatabaseCount(ServiceNowRequest::class, 4);

        $this->assertDatabaseCount(FirewallRule::class, 7);
    }


    /** @test */
    public function import_firewall_requests_will_be_logged()
    {
        Log::shouldReceive('info')->between(7, 7)->withArgs(function ($message) {
            return str_contains($message, 'Create rule') === true;
        });

        Log::shouldReceive('debug')->once()->withArgs(function ($message) {
            return str_contains($message, 'Overwrite "End-date" due to an invalid source value') === true;
        });

        Log::shouldReceive('info')->between(4, 4)->withArgs(function ($message) {
            return str_contains($message, 'Request_Firewall') === true;
        });

        Log::shouldReceive('error')->between(0, 0);

        $this->importOneFile();
    }

    /** @test */
    public function import_firewall_rules_expect_queued_import_jobs()
    {
        Queue::fake();

        $this->importOneFile();

        Queue::assertPushed(ImportServiceNowFirewallRequestsJob::class, 4);
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

        $this->importOneFile();

        Notification::assertSentTo([User::first()], FirewallRequestsImportedNotification::class, 1);

    }


    /** @test */
    public function add_missing_reviewers_to_the_app()
    {
        $this->importOneFile('valid_with_users_1.json');

        User::where(function ($query) {
            $query->where('provider_id', '7dc98c09-d66f-4bdb-aa42-6b01b105af04')
                ->orWhere('provider_id', '7761796b-20da-4c22-9497-485df7e7a7c8');
        })->delete();

        $emails = ServiceNowRequest::whereRelation('rules', 'pci_dss', true)
            ->get()
            ->unique('requestor_mail')
            ->map
            ->requestor_mail
            ->toArray();

        $scim = new Scim();
        $scim->provider('lhg_graph')
            ->users($emails)
            ->add();

        foreach ($emails as $email) {
            $user = User::whereEmail($email)->first();
            $this->assertEquals($email, $user->email);
            $this->assertEquals(true, $user->status);
        }


    }

    protected function importOneFile(string $file = '')
    {
        $file = !empty($file) ? $file : 'valid_1.json';
        Storage::fake('tmp-for-tests');
        $first = file_get_contents(base_path() . '/tests/Feature/Stubs/firewallImport/' . $file);
        $files[] = UploadedFile::fake()->createWithContent('valid_1.json', $first);

        $user = User::first();
        $user->assignRole('Firewall Administrator');
        Livewire::actingAs($user)->test(FirewallRequestsImport::class)
            ->set('attachments', $files)
            ->assertHasNoErrors()
            ->call('save');
    }
}
