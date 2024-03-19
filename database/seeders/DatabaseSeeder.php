<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            //UserSeeder::class,
            UserAzureSeeder::class,
            TokenCacheProviderSeeder::class,
            OperationSeeder::class,
            RoleSeeder::class,
            //BusinessServiceSeeder::class
            SubnetSeeder::class,
        ]);
    }
}
