<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Tests\Feature\Models;

use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_user(): void
    {
        $this->assertDatabaseCount(User::class, 0);

        User::factory()->create();

        $this->assertDatabaseCount(User::class, 1);
    }

    /** @test */
    public function test_groups_and_roles_relations(): void
    {
        $user = User::factory()->make();

        $this->assertCount(0, $user->groups()->get());
        $this->assertCount(0, $user->roles()->get());
    }

    /** @test */
    public function user_is_owner_of_a_group(): void
    {
        $user = User::factory()->withGroup()->create();
        $user->groups()->first()->attachOwners($user);

        $this->assertEquals(
            Group::first()->name,
            $user->groups('owner')->first()->name
        );
    }

    /** @test */
    public function can_remove_role_from_user_by_name(): void
    {
        $user = User::factory()->withRole()->create();

        $user->unassignRole($user->roles()->first()->name);

        $this->assertCount(0, $user->roles()->get());
    }

    /** @test */
    public function can_remove_group_from_user_by_name(): void
    {
        $user = User::factory()->withGroup()->create();

        $user->unassignGroup($user->groups()->first()->name);

        $this->assertCount(0, $user->groups()->get());
    }

    /** @test */
    public function can_create_user_with_roles_and_groups(): void
    {
        User::factory()
            ->count(2)
            ->withRole(2)
            ->withGroup(2)
            ->create();

        $this->assertDatabaseCount(User::class, 2);
        $this->assertDatabaseCount(Role::class, 4);
        $this->assertDatabaseCount(Group::class, 4);
    }

    /** @test */
    public function can_list_all_roles_attached_to_a_user(): void
    {
        $user = User::factory()->withRole(2)->create();

        $this->assertCount(2, $user->allRoles());
    }

    /** @test */
    public function can_list_all_operations_assigned_to_a_user(): void
    {
        $user = User::factory()->create();

        $roles = Role::factory()->withOperations(5)->create();

        $group = Group::factory()->create();

        $group->attachUsers($user);
        $group->attachRoles($roles);

        $this->assertGreaterThanOrEqual(4, $user->operations()->count());
    }
}
