<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Admin\GroupsCreate;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserAzureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\FrontendTest;
use Tests\Helper;
use Tests\TestCase;

class GroupsCreateTest extends TestCase implements FrontendTest
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
        $this->get('/admin/group/create')->assertRedirect('/login');
    }

    /** @test */
    public function can_access_route_as_user(): void
    {
        User::first()->assignRole('Group Operator');
        $this->actingAs(User::first())
            ->get('/admin/group/create')
            ->assertOk();
    }

    /** @test */
    public function can_render_the_component(): void
    {
        User::first()->assignRole('Group Operator');
        Livewire::actingAs(User::first())
            ->test(GroupsCreate::class)
            ->assertOk();
    }

    /** @test */
    public function can_view_component(): void
    {
        User::first()->assignRole('Group Administrator');
        $this->actingAs(User::first())
            ->get('/admin/group/create')
            ->assertSeeLivewire('admin.groups-create')
            ->assertSee('Create Group')
            ->assertSee('Group Details');
    }

    /** @test */
    public function can_create_a_group(): void
    {
        User::first()->assignRole('Group Administrator');
        Livewire::actingAs(User::first())
            ->test(GroupsCreate::class)
            ->set('group.name', 'A new test-group')
            ->set('group.description', 'A new test-group description')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatchedBrowserEvent('notify', ['message' => 'Saved', 'type' => 'success'])
            ->assertRedirect(route('admin.group'));

    }
}
