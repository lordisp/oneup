<?php

namespace Database\Seeders;

use App\Models\Operation;
use Illuminate\Database\Seeder;

class OperationSeeder extends Seeder
{

    protected array $operations = [
        'admin/rbac/operation/read' => 'Can read operation',
        'admin/rbac/operation/readAll' => 'Can read all operations',
        'admin/rbac/operation/create' => 'Can create operations',
        'admin/rbac/operation/delete' => 'Can delete operations',
        'admin/rbac/operation/update' => 'Can update operations',
        'admin/rbac/role/readAll' => 'Can read all roles',
        'admin/rbac/role/create' => 'Can create roles',
        'admin/rbac/role/delete' => 'Can read roles',
        'admin/rbac/role/update' => 'Can update operations',
        'admin/tokenCacheProvider/read' => 'Can read Provider',
        'admin/tokenCacheProvider/create' => 'Can create Provider',
        'admin/tokenCacheProvider/delete' => 'Can delete Provider',
        'admin/tokenCacheProvider/readAll' => 'Can read all Providers',
        'admin/rbac/group/readAll' => 'Can read all groups',
        'admin/rbac/group/create' => 'Can create groups',
        'admin/rbac/group/delete' => 'Can read groups',
        'admin/rbac/group/update' => 'Can update groups',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->operations as $key => $value) {
            Operation::factory()->state([
                'operation' => $key, 'description' => $value
            ])->create();
        }
    }
}
