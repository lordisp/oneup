<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\Roles;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;

class RolesTest extends TestCase implements FrontendTest
{
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UserAzureSeeder::class, OperationSeeder::class, RoleSeeder::class]);
    }

    /** @test */
    public function cannot_access_route_as_guest(): void
    {
        $this->get('/admin/roles')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user(): void
    {
        $user = User::first();
        $user->assignRole('Roles reader');
        $this->actingAs($user)
            ->get('/admin/roles')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component(): void
    {
        $user = User::first();
        $user->assignRole('Roles reader');
        Livewire::actingAs($user)->test(Roles::class)->assertOk();
    }

    /** @test */
    public function can_view_component(): void
    {
        $user = User::first();
        $user->assignRole('Roles reader');
        $this->actingAs($user)
            ->get('/admin/roles')
            ->assertSeeLivewire('admin.roles')
            ->assertSee(__('button.role_create'))
            ->assertDontSee(__('empty-table.admin_provider', ['attribute' => 'roles']));
    }

    /** @test */
    public function can_delete_a_role(): void
    {
        $user = User::first();
        $user->assignRole('Roles administrator');
        $role = Role::where('name', 'like', 'Provider reader')->first();
        $this->assertDatabaseCount(Role::class, 19);

        Log::shouldReceive('info')->once()->withArgs(function ($message, $context) use ($role) {
            return str_contains($message, 'Destroy Role') == true
                && $context['Trigger'] == auth()->user()->getAuthIdentifier()
                && $context['Resource'][0]['id'] == $role->id;
        });

        Livewire::actingAs($user)
            ->test(Roles::class)
            ->set('selected', [$role->id])
            ->call('deleteModal')
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'delete'])
            ->call('delete')
            ->assertDispatchedBrowserEvent('notify', ['message' => __('messages.deleted'), 'type' => 'success']);
        $this->assertDatabaseCount(Role::class, 18);
    }
}
