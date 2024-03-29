<?php

namespace Tests\Feature\Database;

use Database\Seeders\DatabaseSeeder;
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
    public function can_seed_users(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseCount('users', 50);
    }

    /** @test */
    public function can_seed_azure_test_users(): void
    {
        $this->seed(UserAzureSeeder::class);

        $this->assertDatabaseCount('users', 4);
    }

    /** @test */
    public function can_seed_groups(): void
    {
        $this->seed(GroupSeeder::class);
        $this->assertDatabaseCount('groups', 10);
    }

    /** @test */
    public function can_seed_roles(): void
    {
        $this->seed(UserAzureSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->assertDatabaseCount('roles', 19);

    }

    /** @test */
    public function can_seed_operations(): void
    {
        $this->seed(OperationSeeder::class);
        $this->assertDatabaseCount('operations', 26);
    }

    /** @test */
    public function can_seed_all(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('token_cache_providers', 5);
        $this->assertDatabaseCount('roles', 19);
        $this->assertDatabaseCount('operations', 26);
        $this->assertDatabaseCount('business_services', 0);
    }
}
