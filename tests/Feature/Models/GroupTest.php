<?php

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Tests\Feature\Models;

use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_group(): void
    {
        $this->assertDatabaseCount(Group::class, 0);

        $group = Group::factory()->create();

        $this->assertDatabaseCount(Group::class, 1);

        $this->assertTrue(Str::isUuid($group->first()->id));
        $this->assertIsString($group->first()->name);
        $this->assertIsString($group->first()->slug);
        $this->assertIsString($group->first()->description);
    }

    /** @test */
    public function can_assign_and_remove_user_from_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->assertCount(0, $group->users()->get());

        $group->attachUsers($user);
        $this->assertCount(1, $group->users()->get());

        $group->detachUsers($user);
        $this->assertCount(0, $group->users()->get());
    }

    /** @test */
    public function can_assign_and_remove_an_owner_from_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->assertCount(0, $group->owners()->get());

        $group->attachOwners($user);
        $this->assertCount(1, $group->owners()->get());

        $group->detachOwners($user);
        $this->assertCount(0, $group->owners()->get());
    }

    /** @test */
    public function can_assign_and_remove_role_from_group(): void
    {
        $group = Group::factory()->create();
        $roles = Role::factory()->count(3)->create();

        $this->assertEmpty($group->roles()->get());

        $group->assignRole($roles->first()->name);
        $group->assignRole($roles);

        $this->assertCount(3, $group->roles()->get());

        $group->unassignRole($roles->first()->name);
        $group->unassignRole($roles);

        $this->assertEmpty($group->roles()->get());
    }
}
