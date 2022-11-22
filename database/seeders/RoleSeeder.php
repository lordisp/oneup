<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\Role;
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
        $this->seedRbacRoles();
        $this->seedRbacOperations();
        $this->seedProvider();
        $this->seedGroup();
    }

    protected function seedRbacRoles()
    {
        Role::factory()->state([
            'name' => 'Roles reader',
            'description' => 'Can read all roles',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/role/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Roles operator',
            'description' => 'Can manage roles',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/role/read%')
            ->orWhere('operation','like','admin/rbac/role/create')
            ->orWhere('operation','like','admin/rbac/role/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Roles administrator',
            'description' => 'Can manage all aspects of roles',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/role%')
            ->get())
            ->create();
    }

    protected function seedRbacOperations()
    {
        Role::factory()->state([
            'name' => 'Operations reader',
            'description' => 'Can read all operations',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/operation/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Operations operator',
            'description' => 'Can create and update operations but cannot delete them.',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/operation/read%')
            ->orWhere('operation','like','admin/rbac/operation/create')
            ->orWhere('operation','like','admin/rbac/operation/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Operations administrator',
            'description' => 'Can manage all aspects of provider',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/operation%')
            ->get())
            ->create();
    }

    protected function seedProvider(){
        Role::factory()->state([
            'name' => 'Provider reader',
            'description' => 'Can read all provider',
        ])->hasAttached(Operation::where('operation','like','admin/tokenCacheProvider/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Provider operator',
            'description' => 'Can create and update provider but cannot delete them.',
        ])->hasAttached(Operation::where('operation','like','admin/tokenCacheProvider/read%')
            ->orWhere('operation','like','admin/tokenCacheProvider/create')
            ->orWhere('operation','like','admin/tokenCacheProvider/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Provider administrator',
            'description' => 'Can manage all aspects of operations',
        ])->hasAttached(Operation::where('operation','like','admin/tokenCacheProvider%')
            ->get())
            ->create();
    }

    protected function seedGroup(){
        Role::factory()->state([
            'name' => 'Group reader',
            'description' => 'Can read all groups',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/group/read%')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Group operator',
            'description' => 'Can create and update groups but cannot delete them.',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/group/read%')
            ->orWhere('operation','like','admin/rbac/group/create')
            ->orWhere('operation','like','admin/rbac/group/update')
            ->get())
            ->create();

        Role::factory()->state([
            'name' => 'Group administrator',
            'description' => 'Can manage all aspects of groups',
        ])->hasAttached(Operation::where('operation','like','admin/rbac/group%')
            ->get())
            ->create();
    }
}
