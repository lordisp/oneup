<?php

namespace Tests\Feature\Ui\Firewall;

use App\Http\Livewire\PCI\FirewallRulesRead;
use App\Models\FirewallRule;
use App\Models\ServiceNowRequest;
use App\Models\User;
use App\Notifications\FirewallReviewRequiredNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\FrontendTest;

class FirewallRulesReadTest extends FirewallRequestImportTest implements FrontendTest
{

    /** @test */
    public function cannot_access_route_as_guest()
    {
        $this->get('/firewall/requests/read')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user()
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $this->actingAs($user)
            ->get('/firewall/requests/read')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component()
    {
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->assertOk()
            ->assertViewIs('livewire.p-c-i.firewall-rules-read');
    }

    /** @test */
    public function can_view_component()
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
    public function can_extend_rules()
    {
        Log::shouldReceive('info')->atMost();
        Log::shouldReceive('error')->atMost();
        Log::shouldReceive('debug')->atMost();

        $this->importOneFile();
        $key = ServiceNowRequest::whereRelation('rules', 'description', 'Access to D rule 1')
            ->first()->rules->first()->id;
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->assertOk()
            ->assertSee('Access to D rule 1')
            ->assertSee('Access to D rule 2')
            ->assertSee('Access to D rule 3')
            ->assertDontSee('FW Connection between E and F')
            ->assertDontSee('to service F rule 1')
            ->assertDontSee('Changed address for Services rule 2')
            ->assertDontSee('You\'re all done')
            ->call('edit', $key)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->call('extendConfirm')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit'])
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'extendConfirm'])
            ->assertSee('Are you sure?')
            ->assertSee('Confirm Extension')
            ->call('extend')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'extendConfirm'])
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Rule has been extended!', 'type' => 'success']);
        $this->assertEquals('extended', FirewallRule::find($key)->status);
    }

    /** @test */
    public function can_decommission_rules()
    {
        Log::shouldReceive('info')->atMost();
        Log::shouldReceive('error')->atMost();
        Log::shouldReceive('debug')->atMost();

        $this->importOneFile();
        $key = ServiceNowRequest::where('description', 'FW Connection between C and D')
            ->first()
            ->rules
            ->first()
            ->id;
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->assertOk()
            ->assertSee('Access to D rule 1')
            ->assertSee('Access to D rule 2')
            ->assertSee('Access to D rule 3')
            ->assertDontSee('FW Connection between E and F')
            ->assertDontSee('to service F rule 1')
            ->assertDontSee('Changed address for Services rule 2')
            ->assertDontSee('You\'re all done')
            ->call('edit', $key)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->call('deleteConfirm')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit'])
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'deleteConfirm'])
            ->assertSee('Are you sure?')
            ->assertSee('Confirm Decommission')
            ->call('delete')
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'deleteConfirm'])
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Rule has been flagged as decommissioned!', 'type' => 'success']);
        $this->assertEquals('deleted', FirewallRule::find($key)->status);
    }


    /** @test */
    public function can_delete_all_requests_by_an_administrartor()
    {
        $this->importOneFile();
        $user = User::first();
        $user->unassignRole('Firewall Administrator');
        $user->assignRole('Firewall-Requests Reader');

        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->call('deleteAll')
            ->assertRedirect(RouteServiceProvider::HOME);

        $user->unassignRole('Firewall-Requests Reader');
        $user->assignRole('Firewall Administrator');
        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->call('deleteAll')
            ->assertDispatchedBrowserEvent('notify', ['message' => 'All records deleted!', 'type' => 'success']);
    }

    /** @test */
    public function can_filter_by_non_pci_and_optional_review()
    {
        $this->importOneFile();

        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $rows = Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->set('filters', ['pci_dss' => "'0'", 'status' => 'open'])
            ->assertSee('to service F rule 1')
            ->get('rows')
            ->pluck('description')
            ->toArray();
        $this->assertCount(2, $rows);
        $this->assertEquals('to service F rule 1', $rows[0]);
        $this->assertEquals('Changed address for Services rule 2', $rows[1]);
    }

    /** @test */
    public function can_filter_by_non_pci_and_decommissioned()
    {
        $this->importOneFile();
        FirewallRule::first()->update(['status' => 'deleted']);
        $user = User::first();
        $user->assignRole('Firewall-Requests Reader');
        $rows = Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->set('filters', ['pci_dss' => "'0'", 'status' => 'open'])
            ->get('rows')
            ->pluck('description')
            ->toArray();
        $this->assertCount(2, $rows);
        $this->assertEquals('to service F rule 1', $rows[0]);
        $this->assertEquals('Changed address for Services rule 2', $rows[1]);
    }

    /** @test */
    public function can_invite_pci_relevant_review()
    {
        Notification::fake();
        $this->importOneFile();
        $user = User::first();
        $user->assignRole('Firewall Administrator');
        Livewire::actingAs($user)->test(FirewallRulesRead::class)
            ->call('sendNotification');

        $users = User::whereIn('email', ServiceNowRequest::whereRelation('rules', function ($query) {
            $query->review();
        })->pluck('requestor_mail'))
            ->get();
        Notification::assertSentTo($users,FirewallReviewRequiredNotification::class);
    }

}