<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory([
            'firstName' => 'Rafael',
            'lastName' => 'Camison',
            'displayName' => 'Camison, Rafael',
            'email' => 'rafael.camison@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$LbNBqJGWrF5WKmkNvmqwFOv/GU/4c.1CazWRFWmvOG9DAj6x5gz7m',
            'remember_token' => Str::random(10),
        ])->create();

        User::factory()->count(49)->create();
    }
}
