<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory([
            'firstName' => 'Rafael',
            'lastName' => 'Camison',
            'email' => 'rafael.camison@gmail.com',
            'email_verified_at' => now(),
            'password' => '$2y$10$LbNBqJGWrF5WKmkNvmqwFOv/GU/4c.1CazWRFWmvOG9DAj6x5gz7m',
            'remember_token' => Str::random(10),
        ])->create();

        User::factory()->count(49)->create();
    }
}
