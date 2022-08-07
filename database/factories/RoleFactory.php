<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Operation;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->jobTitle(),
            'description' => $this->faker->sentence()
        ];
    }

    public function withGroup()
    {
        return $this->afterCreating(function (Role $role) {
            Group::factory()->create()->attachRoles($role);
        });
    }

    public function withOperations($count = 1)
    {
        return $this->afterCreating(function (Role $role) use ($count) {
            $role->attach(Operation::factory()->count($count)->create());
        });
    }
}
