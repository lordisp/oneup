<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Tests\Feature\Models;

use App\Models\Group;
use App\Models\Operation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_role()
    {
        $role = Role::factory()->create();

        $this->assertDatabaseCount(Role::class, 1);

        $this->assertTrue(Str::isUuid($role->first()->id));
        $this->assertIsString($role->first()->name);
        $this->assertIsString($role->first()->slug);
        $this->assertIsString($role->first()->description);
    }

    /** @test */
    public function can_list_roles_users()
    {
        $role = Role::factory()->create();
        $users = User::factory()->count(2)->create();
        foreach ($users as $user) {
            $user->assignRole($role);
        }

        $this->assertCount(2, $role->users()->get());
    }

    /** @test */
    public function can_assign_and_remove_user_a_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $this->assertCount(0, $role->users()->get());

        $user->assignRole($role);
        $this->assertCount(1, $role->users()->get());

        $user->unassignRole($role);
        $this->assertCount(0, $role->users()->get());
    }

    /** @test */
    public function can_create_role_with_group()
    {
        Role::factory()->withGroup()->create()->groups();
        $this->assertDatabaseCount(Role::class, 1);
        $this->assertDatabaseCount(Group::class, 1);
    }

    /** @test */
    public function can_create_role_with_operation()
    {
        Role::factory()->withOperations()->create();
        $this->assertDatabaseCount(Role::class, 1);
        $this->assertDatabaseCount(Operation::class, 1);
    }

    /** @test */
    public function can_remove_operation_from_role()
    {
        $role = Role::factory()->withOperations()->create();

        $this->assertDatabaseCount(Role::class, 1);
        $this->assertDatabaseCount(Operation::class, 1);

        $role->detach($role->operations()->first());
        $this->assertEmpty($role->operations()->get());
    }
}
