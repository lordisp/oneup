<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedGlobalAdmin();
        $this->seedRbacRoles();
        $this->seedRbacOperations();
        $this->seedProvider();
        $this->seedGroup();
        $this->seedFireWall();
        $this->seedUser();
        User::first()->assignRole('Global Administrator');
    }

    protected function seedGlobalAdmin()
    {
        Role::factory()->state([
            'name' => 'Global Administrator',
            'description' => 'God father of OneUp',
        ])->hasAttached(Operation::where('operation', 'like', '%')
            ->get())
            ->create();
    }

    protected function seedRbacRoles()
    {
        Role::factory()->state([
            'name' => 'Roles reader',
            'description' => 'Can read all roles',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/role/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Roles operator',
            'description' => 'Can manage roles',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/role/read%')
            ->orWhere('operation', 'like', 'admin/rbac/role/create')
            ->orWhere('operation', 'like', 'admin/rbac/role/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Roles administrator',
            'description' => 'Can manage all aspects of roles',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/role%')
            ->get())
            ->create();
    }

    protected function seedRbacOperations()
    {
        Role::factory()->state([
            'name' => 'Operations reader',
            'description' => 'Can read all operations',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/operation/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Operations operator',
            'description' => 'Can create and update operations but cannot delete them.',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/operation/read%')
            ->orWhere('operation', 'like', 'admin/rbac/operation/create')
            ->orWhere('operation', 'like', 'admin/rbac/operation/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Operations Administrator',
            'description' => 'Can manage all aspects of provider',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/operation%')
            ->get())
            ->create();
    }

    protected function seedProvider()
    {
        Role::factory()->state([
            'name' => 'Provider reader',
            'description' => 'Can read all provider',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/tokenCacheProvider/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Provider Operator',
            'description' => 'Can create and update provider but cannot delete them.',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/tokenCacheProvider/read%')
            ->orWhere('operation', 'like', 'admin/tokenCacheProvider/create')
            ->orWhere('operation', 'like', 'admin/tokenCacheProvider/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Provider Administrator',
            'description' => 'Can manage all aspects of operations',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/tokenCacheProvider%')
            ->get())
            ->create();
    }

    protected function seedGroup()
    {
        Role::factory()->state([
            'name' => 'Group Reader',
            'description' => 'Can read all groups',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/group/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Group Operator',
            'description' => 'Can create and update groups but cannot delete them.',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/group/read%')
            ->orWhere('operation', 'like', 'admin/rbac/group/create')
            ->orWhere('operation', 'like', 'admin/rbac/group/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Group Administrator',
            'description' => 'Can manage all aspects of groups',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/group%')
            ->get())
            ->create();
    }

    protected function seedFireWall()
    {
        Role::factory()->state([
            'name' => 'Firewall Administrator',
            'description' => 'Can manage all aspects of groups',
        ])->hasAttached(Operation::where('operation', 'like', 'service-now/firewall%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Firewall-Requests Reader',
            'description' => 'Can read all Service-Now Firewall requests',
        ])->hasAttached(Operation::where('operation', 'like', 'service-now/firewall/request/readAll')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Firewall-Requests Operator',
            'description' => 'Can manage all aspects of Service-Now Firewall requests besides deleting them.',
        ])->hasAttached(Operation::where(function ($query) {
            $query->where('operation', 'like', 'service-now/firewall/import')
                ->orWhere('operation', 'like', 'service-now/firewall/request/readAll');
        })
            ->get())
            ->create();
    }

    protected function seedUser()
    {
        Role::factory()->state([
            'name' => 'User Administrator',
            'description' => 'Can manage all aspects of users',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/user%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'User Reader',
            'description' => 'Can read all Users',
        ])->hasAttached(Operation::where('operation', 'like', 'admin/rbac/user/readAll')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'User Support',
            'description' => 'Can login as a given user.',
        ])->hasAttached(Operation::where(function ($query) {
            $query->where('operation', 'like', 'admin/rbac/user/readAll')
                ->orWhere('operation', 'like', 'admin/rbac/user/loginAs');
        })
            ->get())
            ->create();
    }
}
