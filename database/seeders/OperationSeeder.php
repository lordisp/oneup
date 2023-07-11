<?php

namespace Database\Seeders;

use App\Models\Operation;
use Illuminate\Database\Seeder;

class OperationSeeder extends Seeder
{

    protected array $operations = [
        /*Operations*/
        'admin/rbac/operation/read' => 'Can read operation',
        'admin/rbac/operation/readAll' => 'Can read all operations',
        'admin/rbac/operation/create' => 'Can create operations',
        'admin/rbac/operation/delete' => 'Can delete operations',
        'admin/rbac/operation/update' => 'Can update operations',
        /* Roles */
        'admin/rbac/role/readAll' => 'Can read all roles',
        'admin/rbac/role/create' => 'Can create roles',
        'admin/rbac/role/delete' => 'Can read roles',
        'admin/rbac/role/update' => 'Can update operations',
        /* Users */
        'admin/rbac/user/readAll' => 'Can read all users',
        'admin/rbac/user/loginAs' => 'Can login as a given user',
        'admin/rbac/user/delete' => 'Can delete user',
        /*TokenCache*/
        'admin/tokenCacheProvider/read' => 'Can read Provider',
        'admin/tokenCacheProvider/create' => 'Can create Provider',
        'admin/tokenCacheProvider/delete' => 'Can delete Provider',
        'admin/tokenCacheProvider/readAll' => 'Can read all Providers',
        /* Groups */
        'admin/rbac/group/readAll' => 'Can read all groups',
        'admin/rbac/group/create' => 'Can create groups',
        'admin/rbac/group/delete' => 'Can read groups',
        'admin/rbac/group/update' => 'Can update groups',
        /* ServiceNow Firewall */
        'serviceNow/firewall/import' => 'Can import firewall-requests from Service-Now',
        'serviceNow/firewall/request/read' => 'Can read own firewall-requests',
        'serviceNow/firewall/request/readAll' => 'Can read all firewall-requests',
        'serviceNow/firewall/request/deleteAll' => 'Can delete all firewall-requests',
        /* Telescope */
        'admin/telescope/view' => 'Can import firewall-requests from Service-Now',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->operations as $key => $value) {
            Operation::updateOrCreate([
                'operation' => $key
            ], [
                'operation' => $key,
                'description' => $value
            ]);

        }
    }
}
