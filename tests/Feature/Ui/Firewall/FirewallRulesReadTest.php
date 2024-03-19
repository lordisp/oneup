<?php

namespace Tests\Feature\Ui\Firewall;

use App\Http\Livewire\PCI\FirewallRulesRead;
use App\Jobs\InviteFirewallReviewerJob;
use App\Jobs\ServiceNow\ImportBusinessServiceMemberJob;
use App\Jobs\ServiceNowDeleteAllJob;
use App\Models\Audit;
use App\Models\FirewallRule;
use App\Models\Group;
use App\Models\ServiceNowRequest;
use App\Models\Subnet;
use App\Models\User;
use App\Notifications\DeveloperNotification;
use App\Notifications\UserActionNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\FrontendTest;

class FirewallRulesReadTest extends FirewallRequestImportTest implements FrontendTest
{
    /** @test */
    public function cannot_access_route_as_guest(): void
    {
        $this->get('/firewall/requests/read')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $this->actingAs($user)
            ->get('/firewall/requests/read')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->assertOk()
            ->assertViewIs('livewire.p-c-i.firewall-rules-read');
    }

    /** @test */
    public function can_view_component(): void
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $this->actingAs($user)
            ->get('/firewall/requests/read')
            ->assertSeeLivewire('p-c-i.firewall-rules-read')
            ->assertSee('Firewall Requests')
            ->assertSee('Status')
            ->assertSee('You\'re all done');
    }

    /** @test */
    public function can_extend_rules(): void
    {
        Subnet::factory([
            'name' => '10.0.0.0',
            'size' => 8,
            'pci_dss' => Carbon::now(),
        ])->create();

        Queue::fake(ImportBusinessServiceMemberJob::class);
        $this->importOneFile();

        $rule = FirewallRule::query()->review()->first();
        $businessService = $rule->businessService;
        $key = $rule->id;
        $user = User::first();
        $user->businessServices()->attach($businessService->id);

        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->assertOk()
            ->call('edit', $key)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->call('extendConfirm')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit'])
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'extendConfirm'])
            ->call('extend')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'extendConfirm'])
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Rule has been extended!', 'type' => 'success']);

        $rule = FirewallRule::find($key);
        $this->assertEquals('extended', $rule->status);
        $this->assertEquals('Extend Firewall-Rule', $rule->audits->last()->activity);
        $this->assertEquals($user->email, $rule->audits->last()->actor);
        $this->assertEquals('Success', $rule->audits->last()->status);
        $this->assertDatabaseCount(Audit::class, 7);
    }

    /** @test */
    public function can_decommission_rules(): void
    {
        Log::shouldReceive('info')->atMost();
        Log::shouldReceive('error')->atMost();
        Log::shouldReceive('debug')->atMost();

        Subnet::factory([
            'name' => '10.0.0.0',
            'size' => 8,
            'pci_dss' => Carbon::now(),
        ])->create();

        Http::fake([config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/request.json')), true)),
        ]);

        Queue::fake([ImportBusinessServiceMemberJob::class]);

        $this->importOneFile();
        $rule = FirewallRule::query()->review()->first();
        $businessService = $rule->businessService;
        $key = $rule->id;
        $user = User::first();
        $user->businessServices()->attach($businessService->id);

        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->assertOk()
            ->call('edit', $key)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->call('deleteConfirm')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit'])
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'deleteConfirm'])
            ->assertSee('Are you sure?')
            ->assertSee('Confirm Decommission')
            ->call('delete')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'deleteConfirm'])
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved...', 'type' => 'success']);
        $this->assertEquals('deleted', FirewallRule::find($key)->status);
    }

    /** @test */
    public function it_deletes_all_requests_write_a_log_and_notify_the_user(): void
    {
        Http::fake([config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);

        $this->importOneFile();
        $user = User::first();
        $this->assertDatabaseCount(ServiceNowRequest::class, 3);
        Notification::fake();
        Log::shouldReceive('info')->once();

        $job = new ServiceNowDeleteAllJob($user);
        $job->handle();
        Notification::assertSentTo($user, UserActionNotification::class);
        Notification::assertNotSentTo($user, DeveloperNotification::class);
        $this->assertDatabaseCount(ServiceNowRequest::class, 0);
    }

    /** @test */
    public function it_fail_to_deletes_requests_write_a_log_and_notify_the_developers(): void
    {
        Subnet::factory([
            'name' => '10.0.0.0',
            'size' => 8,
            'pci_dss' => Carbon::now(),
        ])->create();
        Queue::fake(ImportBusinessServiceMemberJob::class);
        $this->importOneFile();
        $rule = FirewallRule::query()->review()->first();
        $businessService = $rule->businessService;
        $user = User::first();
        $user->businessServices()->attach($businessService->id);
        $this->createDeveloperGroup();

        $user->assignGroup('Developers');
        $this->assertDatabaseCount(ServiceNowRequest::class, 3);

        Notification::fake();
        Log::shouldReceive('error')->once();
        $exception = 'An error occurred';

        $job = new ServiceNowDeleteAllJob($user);
        $job->failed($exception);

        Notification::assertSentTo($user, DeveloperNotification::class);
        Notification::assertNotSentTo($user, UserActionNotification::class);
        $this->assertDatabaseCount(ServiceNowRequest::class, 3);
    }

    /** @test */
    public function can_delete_all_requests_by_an_administrartor(): void
    {
        Http::fake([config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);

        $this->importOneFile();
        $this->createDeveloperGroup();
        $user = User::first();
        $user->unassignRole('Firewall Administrator');
        $user->assignRole('Firewall-Requests Reader');
        $user->assignGroup('Developers');

        Notification::fake();

        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->call('deleteAll')
            ->assertStatus(403);

        Notification::assertNotSentTo($user, UserActionNotification::class);
        Notification::assertNotSentTo($user, DeveloperNotification::class);

        $user->unassignRole('Firewall-Requests Reader');
        $user->assignRole('Firewall Administrator');

        $component = Livewire::actingAs(User::first())->test(FirewallRulesRead::class)
            ->call('deleteAll');
        $component->assertDispatchedBrowserEvent('notify', ['message' => __('messages.start_delete_all_requests'), 'type' => 'success']);

        Notification::assertSentTo($user, UserActionNotification::class, function ($context) {
            return $context->message === __('messages.all_requests_deleted');
        });

        Notification::assertSentTo($user, UserActionNotification::class, function ($context) {
            return $context->message === __('messages.all_requests_deleted');
        });
        Notification::assertNotSentTo($user, DeveloperNotification::class);
    }

    /** @test */
    public function can_filter_by_non_pci_and_optional_review(): void
    {
        Http::fake([config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);

        $this->importOneFile();

        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $rows = Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->set('filters', ['pci_dss' => "'0'", 'status' => 'open', 'own' => "'1'"])
            ->assertSee('RITM0073261')
            ->get('rows')
            ->pluck('description')
            ->toArray();
        $this->assertCount(3, $rows);
        $this->assertEquals('FMG to Kusco', $rows[0]);
        $this->assertEquals('LCA connection to HUBSTAR databases', $rows[1]);
    }

    /** @test */
    public function can_filter_by_non_pci_and_decommissioned(): void
    {
        Http::fake([config('servicenow.uri').'/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/bs01.json')), true))]);
        Http::fake(['https://login.microsoftonline.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/app_access_token.json')), true))]);
        Http::fake(['https://graph.microsoft.com/*' => Http::response(json_decode(file_get_contents(base_path('/tests/Feature/Stubs/ServiceNow/user1.json')), true))]);
        $this->importOneFile();

        FirewallRule::first()->update(['status' => 'deleted']);
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $rows = Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->set('filters', ['pci_dss' => "'0'", 'status' => 'open', 'own' => "'1'"])
            ->get('rows')
            ->pluck('description')
            ->toArray();
        $this->assertCount(3, $rows);
        $this->assertEquals('FMG to Kusco', $rows[0]);
        $this->assertEquals('LCA connection to HUBSTAR databases', $rows[1]);
    }

    /** @test */
    public function can_send_firewall_review_email(): void
    {
        $user = User::first();
        $user->assignRole('Global Administrator');

        Queue::fake([InviteFirewallReviewerJob::class]);
        Log::shouldReceive('info')->with(__('messages.dispatched_firewall_review_mails', ['email' => $user->email]), []);

        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->call('sendNotification');

        Queue::assertPushed(InviteFirewallReviewerJob::class, 1);
    }

    protected function createDeveloperGroup()
    {
        Group::factory()->state([
            'name' => 'Developers',
            'description' => 'OneUp Developers',
        ])->create();
    }
}
