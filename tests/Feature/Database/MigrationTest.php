<?php

namespace Tests\Feature\Database;

use Database\Seeders\GroupSeeder;
use Database\Seeders\OperationSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserAzureSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_seed_users()
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 50);
    }

    /** @test */
    public function can_seed_azure_test_users()
    {
        $this->seed(UserAzureSeeder::class);

        $this->assertDatabaseCount('users', 3);
    }

    /** @test */
    public function can_seed_groups()
    {
        $this->seed(GroupSeeder::class);
        $this->assertDatabaseCount('groups', 10);
    }

    /** @test */
    public function can_seed_roles()
    {
        $this->seed(RoleSeeder::class);
        $this->assertDatabaseCount('roles', 10);

    }

    /** @test */
    public function can_seed_operations()
    {

        $this->seed(OperationSeeder::class);
        $this->assertDatabaseCount('operations', 10);
    }

}
