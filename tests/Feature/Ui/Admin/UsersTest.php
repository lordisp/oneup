<?php

namespace Tests\Feature\Ui\Admin;

use App\Http\Livewire\Rbac\Users;
use App\Models\Operation;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\Helper;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use Helper, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->count(30)->state(['password' => Hash::make(md5(config('app.key')))])->create();
        $this->seed([
            OperationSeeder::class,
            RoleSeeder::class,
        ]);
    }

    /** @test */
    public function is_shows_15_of_30_users()
    {
        Livewire::actingAs(User::first())->test(Users::class)
            ->assertViewIs('livewire.rbac.users')
            ->assertSee('First name')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Showing 15 of 30');
    }

    /** @test */
    public function can_search_for_users()
    {
        $otherUser = User::skip(1)->first();
        Livewire::actingAs(User::first())->test(Users::class)
            ->assertViewIs('livewire.rbac.users')
            ->set('search', $otherUser->email)
            ->assertPayloadSet('search', $otherUser->email)
            ->assertSee($otherUser->firstName)
            ->assertSee($otherUser->lastName)
            ->assertSee($otherUser->email)
            ->assertSee('Showing 1 of 1')
            ->call('clearSearch')
            ->assertSee('Showing 15 of 30');
    }

    /** @test */
    public function can_login_as_other_user()
    {
        $user = User::first();
        $otherUser = User::skip(1)->first();
        Livewire::actingAs($user)->test(Users::class)
            ->call('loginAs', $otherUser->id)
            ->assertRedirect('/dashboard')
            ->assertOk();
        $this->assertAuthenticatedAs($otherUser);
    }

    /** @test */
    public function can_lockout_other_users_sessions()
    {
        $user = User::first();
        $operation = Operation::factory()->state([
            'operation' => 'admin/rbac/user/lockUser',
            'description' => fake()->text(),
        ])->create();
        Role::whereName('Global Administrator')->first()->attach($operation);
        $otherUser = User::skip(1)->first();

        Livewire::actingAs($user)->test(Users::class)
            ->call('openLogoutUserModal', $otherUser->id)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'logout-user'])
            ->call('logoutUser')
            ->assertDispatchedBrowserEvent('notify', ['message' => "{$otherUser->email} has been logged out.", 'type' => 'success'])
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'logout-user']);

        // assert target user is still an active user
        $this->assertEquals(1, User::whereId($otherUser->id)->first()->status);
    }

    /** @test */
    public function can_lockout_users_session_and_disable_account()
    {
        $user = User::first();
        $operation = Operation::factory()->state([
            'operation' => 'admin/rbac/user/lockUser',
            'description' => fake()->text(),
        ])->create();
        Role::whereName('Global Administrator')->first()->attach($operation);
        $otherUser = User::skip(1)->first();

        Livewire::actingAs($user)->test(Users::class)
            ->call('openLogoutUserModal', $otherUser->id)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'logout-user'])
            ->set('modalLock', true)
            ->call('logoutUser')
            ->assertDispatchedBrowserEvent('notify', ['message' => "{$otherUser->email} has been logged out.", 'type' => 'success'])
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'logout-user']);

        // assert target user has been disabled
        $this->assertEquals(0, User::whereId($otherUser->id)->first()->status);
    }

    /** @test */
    public function can_abort_lockout_other_users()
    {
        $user = User::first();
        $operation = Operation::factory()->state([
            'operation' => 'admin/rbac/user/lockUser',
            'description' => fake()->text(),
        ])->create();
        Role::whereName('Global Administrator')->first()->attach($operation);
        $otherUser = User::skip(1)->first();

        Livewire::actingAs($user)->test(Users::class)
            ->call('openLogoutUserModal', $otherUser->id)
            ->assertDispatchedBrowserEvent('open-modal', ['modal' => 'logout-user'])
            ->call('closeLogoutUserModal')
            ->assertPayloadSet('modalLock', false)
            ->assertDispatchedBrowserEvent('close-modal', ['modal' => 'logout-user'])
            ->assertEmitted('refresh');
    }
}
