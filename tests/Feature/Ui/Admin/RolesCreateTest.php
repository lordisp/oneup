<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\RolesEdit;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;

class RolesCreateTest extends TestCase implements FrontendTest
{
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            UserAzureSeeder::class,
            OperationSeeder::class,
            RoleSeeder::class,
        ]);
    }

    /** @test */
    public function cannot_access_route_as_guest(): void
    {
        $this->get('/admin/roles/create')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user(): void
    {
        User::first()->assignRole('Roles operator');
        $this->actingAs(User::first())
            ->get('/admin/roles/create')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component(): void
    {
        User::first()->assignRole('Roles operator');
        Livewire::actingAs(User::first())
            ->test(RolesEdit::class)
            ->assertOk();
    }

    /** @test */
    public function can_view_component(): void
    {
        User::first()->assignRole('Roles administrator');
        $this->actingAs(User::first())
            ->get('/admin/roles/create')
            ->assertSeeLivewire('admin.roles-edit')
            ->assertSee('Create Role')
            ->assertSee('Operations');
    }

    /** @test */
    public function can_create_a_new_role(): void
    {
        $this->assertDatabaseCount(Role::class, 19);
        User::first()->assignRole('Roles operator');
        $role = Livewire::actingAs(User::first())
            ->test(RolesEdit::class)
            ->set('role.name', 'Test Role')
            ->set('role.description', 'This is a Test-Role')
            ->set('selectPage', true)
            ->call('save')
            ->assertHasNoErrors()
            ->get('role');
        $this->assertInstanceOf(Role::class, $role);
        $this->assertDatabaseCount(Role::class, 20);
    }

    /** @test */
    public function create_a_new_role_has_errors(): void
    {
        $this->assertDatabaseCount(Role::class, 19);
        User::first()->assignRole('Roles administrator');
        Livewire::actingAs(User::first())
            ->test(RolesEdit::class)
            ->set('role.name', Str::random(4))
            ->set('role.description', Str::random(4))
            ->call('save')
            ->assertHasErrors([
                'role.name' => ['min'],
                'role.description' => ['min'],
                'selected' => ['required'],
            ])
            ->get('role');
        $this->assertDatabaseCount(Role::class, 19);
    }
}
