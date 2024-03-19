<?php

namespace Database\Seeders;

use App\Models\Operation;
use App\Models\Role;
use Illuminate\Database\Seeder;

class GlobalAdminSeeder extends Seeder
{
    protected array $operations = [
        'admin/rbac/operations/read' => 'Can read operations',
        'admin/rbac/operations/create' => 'Can create operations',
        'admin/rbac/operations/delete' => 'Can delete operations',
        'admin/rbac/operations/update' => 'Can update operations',
        'admin/rbac/role/readAll' => 'Can read all roles',
        'admin/rbac/role/read' => 'Can read roles',
        'admin/rbac/role/delete' => 'Can delete roles',
        'admin/rbac/role/update' => 'Can update operations',
        'admin/tokenCacheProvider/read' => 'Can read Provider',
        'admin/tokenCacheProvider/create' => 'Can create Provider',
        'admin/tokenCacheProvider/delete' => 'Can delete Provider',
        'admin/tokenCacheProvider/readAll' => 'Can read all Providers',
        'serviceNow-firewallRequests-invite' => 'Can invite firewall-reviewers',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::factory()->state([
            'name' => 'Global Administrator', 'description' => 'Can manage all features in '.config('app.name'),
        ])->create();
        foreach ($this->operations as $key => $value) {
            $operation = Operation::factory()->state([
                'operation' => $key, 'description' => $value,
            ])->create();
            $role->attach($operation);
        }
    }
}
