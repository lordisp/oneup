<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\Provider;
use App\Models\TokenCacheProvider;
use App\Models\User;
use Database\Seeders\GlobalAdminSeeder;
use Database\Seeders\TokenCacheProviderSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;

class ProviderTest extends TestCase implements FrontendTest
{
    use Helper, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function cannot_access_route_as_guest()
    {
        $this->get('/admin/provider')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user()
    {
        $this->actingAs($this->user)->get('/admin/provider')->assertOk();
    }

    /** @test */
    public function can_render_the_component()
    {
        Livewire::actingAs($this->user)->test(Provider::class)->assertOk();
    }

    /** @test */
    public function can_view_component()
    {
        $this->actingAs($this->user)->get('/admin/provider')
            ->assertSeeLivewire('admin.provider')
            ->assertSee(__('button.provider_create'))
            ->assertSee(__('empty-table.admin_provider', ['attribute' => 'providers']))
            ->assertSee(__('form.search'));
    }

    /** @test */
    public function can_create_new_provider()
    {
        $this->assertDatabaseCount(TokenCacheProvider::class, 0);

        $this->actingAs($this->user);

        $provider = Livewire::test(Provider::class)
            ->set('provider.name', 'demos')
            ->set('provider.auth_url', '/oauth2/authorize')
            ->set('provider.token_url', '/oauth2/token')
            ->set('provider.auth_endpoint', 'https://login.microsoftonline.com/')
            ->set('type', 'arm')
            ->set('client.tenant', Str::uuid()->toString())
            ->set('client.client_id', Str::uuid()->toString())
            ->set('client.client_secret', Str::random(40))
            ->set('client.resource', 'https://management.azure.com')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'create'])
            ->assertSet('client', [])
            ->assertSet('type', '')
            ->assertSet('search', '')
            ->get('provider');

        $this->assertInstanceOf(TokenCacheProvider::class, $provider);

        $this->assertDatabaseCount(TokenCacheProvider::class, 1);
    }

    /** @test */
    public function create_new_provider_has_errors()
    {
        $this->actingAs($this->user);

        Livewire::test(Provider::class)
            ->set('provider.name', 'demo')
            ->set('provider.auth_url', Str::random(4))
            ->set('provider.token_url', Str::random(4))
            ->set('provider.auth_endpoint', '')
            ->set('type', 'arm')
            ->set('client.tenant', Str::random(40))
            ->set('client.client_id', Str::random(40))
            ->set('client.client_secret', Str::random(15))
            ->set('client.resource', '')
            ->call('save')
            ->assertHasErrors([
                'provider.name' => ['min'],
                'provider.auth_url' => ['min'],
                'provider.token_url' => ['min'],
                'provider.auth_endpoint' => ['required'],
                'client.tenant' => ['uuid'],
                'client.client_id' => ['uuid'],
                'client.client_secret' => ['min'],
                'client.resource' => ['required_without'],
            ]);
    }

    /** @test */
    public function can_delete_a_provider()
    {
        $this->seed(TokenCacheProviderSeeder::class);

        $this->assertDatabaseCount(TokenCacheProvider::class, 5);

        $providerId = TokenCacheProvider::first()->id;

        $this->seed(GlobalAdminSeeder::class);

        $this->user->assignRole('Global Administrator');

        Log::shouldReceive('info')->once()->withArgs(function ($message) {
            return str_contains($message, 'Destroy Token-Cache Provider') == true;
        });

        Livewire::actingAs($this->user)
            ->test(Provider::class)
            ->call('deleteModal', $providerId)
            ->assertCount('objects', 1)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->call('deleteProvider')
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.deleted'), 'type' => 'success'])
            ->assertPayloadSet('selected', [])
            ->assertPayloadSet('selectedPage', false)
            ->assertPayloadSet('selectAll', false);
        $this->assertDatabaseCount(TokenCacheProvider::class, 4);
    }

    /** @test */
    public function can_edit_a_provider()
    {
        $this->seed(TokenCacheProviderSeeder::class);

        $this->actingAs($this->user);

        $provider = TokenCacheProvider::first();

        Livewire::test(Provider::class)
            ->call('editModal', $provider)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'edit'])
            ->assertSee($provider->name)
            ->assertSee(json_decode($provider->client)->tenant)
            ->assertSee(json_decode($provider->client)->client_id)
            ->assertSee(json_decode($provider->client)->client_id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved', 'type' => 'success'])
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'edit']);

    }

    /** @test */
    public function test_search_bar()
    {
        $this->seed(TokenCacheProviderSeeder::class);

        $this->actingAs($this->user);

        Livewire::test(Provider::class)
            ->set('search', 'lhg_arm')
            ->assertSee('lhg_arm')
            ->assertDontSee('lhtest')
            ->set('search', 'lhtest')
            ->assertSee('lhtest')
            ->assertDontSee('azure_ad')
            ->set('search', config('tokencache.azure_ad.client.tenant'))
            ->assertSee('azure')
            ->assertSee(config('tokencache.azure_ad.client.tenant'))
            ->call('clearSearch')
            ->assertCount('queryRows', 5);
    }

    /** @test */
    public function test_pagination()
    {
        TokenCacheProvider::factory()->count(16)->create();

        $this->actingAs($this->user);

        Livewire::test(Provider::class)
            ->assertSee('Showing 15 of 16')
            ->assertSee('Next')
            ->assertDontSee(__('pagination.previous'))
            ->call('gotoPage', 2)
            ->assertSee(__('pagination.previous'))
            ->set('perPage', 16)
            ->assertSee('Showing 16 of 16');
    }
}
