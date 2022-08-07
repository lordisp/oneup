<?php

namespace Tests\Feature\Profile;

use App\Http\Livewire\Profile\Clients;
use App\Models\Passport\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;


class ClientsTest extends TestCase implements FrontendTest
{
    use RefreshDatabase, Helper;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }


    /** @test */
    public function cannot_access_route_as_guest()
    {
        $this->get('/profile/clients')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user()
    {
        $this->actingAs($this->user)->get('/profile/clients')->assertOk();
    }

    /** @test */
    public function can_render_the_component()
    {
        Livewire::actingAs($this->user)->test(Clients::class)->assertOk();
    }

    /** @test */
    public function can_view_component()
    {
        $component = $this->actingAs($this->user)->get('/profile/clients');
        $component->assertSeeLivewire('profile.clients');
        $component->assertSee(__('button.clients_create'));
        $component->assertSee(__('empty-table.oauth_clients'));
        $component->assertSee(__('form.search'));
    }

    /** @test */
    public function create_new_client_validation_test()
    {
        $this->actingAs($this->user);
        Livewire::test(Clients::class)
            ->set('name')
            ->set('redirect')
            ->call('createClient')
            ->assertHasErrors(['name' => 'required', 'redirect' => 'required'])
            ->set('redirect', 'localhost.com')
            ->set('name', Str::random(192))
            ->call('createClient')
            ->assertHasErrors(['name' => 'max:191', 'redirect' => 'url']);
    }

    /** @test */
    public function can_create_new_client()
    {
        $this->actingAs($this->user);
        $secret = Livewire::test(Clients::class)
            ->set('name', 'my client')
            ->set('redirect', 'https://localhost')
            ->call('createClient')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'create'])
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'secret'])
            ->get('secret');
        $this->assertArrayHasKey('secret', $secret);
        $this->assertArrayHasKey('user_id', $secret);
        $this->assertArrayHasKey('name', $secret);
        $this->assertArrayHasKey('provider', $secret);
        $this->assertArrayHasKey('redirect', $secret);
        $this->assertArrayHasKey('personal_access_client', $secret);
        $this->assertArrayHasKey('password_client', $secret);
        $this->assertArrayHasKey('revoked', $secret);
        $this->assertArrayHasKey('id', $secret);
        $this->assertArrayHasKey('updated_at', $secret);
        $this->assertArrayHasKey('created_at', $secret);

        $this->assertIsString($secret['secret']);
        $this->assertEquals($this->user->getAuthIdentifier(), $secret['user_id']);
        $this->assertIsString($secret['name']);
        $this->assertIsString($secret['redirect']);
        $this->assertNull($secret['provider']);
        $this->assertFalse($secret['personal_access_client']);
        $this->assertFalse($secret['password_client']);
        $this->assertFalse($secret['revoked']);
        $this->assertTrue(Str::isUuid($secret['id']));
    }

    /** @test */
    public function can_create_new_pkce_client()
    {
        $this->actingAs($this->user);
        $secret = Livewire::test(Clients::class)
            ->set('name', 'my client')
            ->set('redirect', 'https://localhost')
            ->set('confidential', true)
            ->call('createClient')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'create'])
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'secret'])
            ->get('secret');
        $this->assertArrayHasKey('secret', $secret);
        $this->assertArrayHasKey('user_id', $secret);
        $this->assertArrayHasKey('name', $secret);
        $this->assertArrayHasKey('provider', $secret);
        $this->assertArrayHasKey('redirect', $secret);
        $this->assertArrayHasKey('personal_access_client', $secret);
        $this->assertArrayHasKey('password_client', $secret);
        $this->assertArrayHasKey('revoked', $secret);
        $this->assertArrayHasKey('id', $secret);
        $this->assertArrayHasKey('updated_at', $secret);
        $this->assertArrayHasKey('created_at', $secret);

        $this->assertNull($secret['secret']);
        $this->assertEquals($this->user->getAuthIdentifier(), $secret['user_id']);
        $this->assertIsString($secret['name']);
        $this->assertIsString($secret['redirect']);
        $this->assertNull($secret['provider']);
        $this->assertFalse($secret['personal_access_client']);
        $this->assertFalse($secret['password_client']);
        $this->assertFalse($secret['revoked']);
        $this->assertTrue(Str::isUuid($secret['id']));
    }

    /** @test */
    public function can_delete_a_client()
    {
        $this->actingAs($this->user);
        $client = Client::factory()->state(['user_id' => $this->user->getAuthIdentifier()])->create();
        $this->assertDatabaseCount(Client::class, 1);

        Log::shouldReceive('info')->once()->withArgs(function ($message) {
            return str_contains($message, 'Destroy client') == true;
        });

        Livewire::test(Clients::class)
            ->call('deleteModal', $client->first()->id)
            ->assertCount('clients', 1)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->call('deleteClient')
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.client_deleted'), 'type' => 'success'])
            ->assertPayloadSet('selected', [])
            ->assertPayloadSet('selectedPage', false)
            ->assertPayloadSet('selectAll', false);
        $this->assertDatabaseCount(Client::class, 0);
    }

    /** @test */
    public function test_search_bar()
    {
        $this->actingAs($this->user);
        $this->post('/oauth/clients', [
            'name' => 'FooClient',
            'redirect' => 'https://foo.com/callback',
        ]);
        $this->post('/oauth/clients', [
            'name' => 'BarClient',
            'redirect' => 'https://bar.com/callback',
        ]);
        Livewire::test(Clients::class)
            ->set('search', 'FooClient')
            ->assertSee('FooClient')
            ->assertDontSee('BarClient')
            ->set('search', 'bar.com')
            ->assertSee('BarClient')
            ->assertDontSee('FooClient')
            ->set('search', 'callback')
            ->assertSee('FooClient')
            ->assertSee('BarClient')
            ->call('clearSearch')
            ->assertCount('queryRows', 2);
    }

    /** @test */
    public function test_pagination()
    {
        $this->actingAs($this->user);
        Client::factory()->count(16)->state(
            ['user_id' => $this->user->getAuthIdentifier()]
        )->create();
        Livewire::test(Clients::class)
            ->assertSee('Showing 15 of 16')
            ->assertSee('Next')
            ->assertDontSee(__('pagination.previous'))
            ->call('gotoPage', 2)
            ->assertSee(__('pagination.previous'))
            ->set('perPage', 16)
            ->assertSee('Showing 16 of 16');
    }
}
