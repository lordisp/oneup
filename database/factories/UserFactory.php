<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'displayName' => $this->faker->name(),
            'provider_id' => $this->faker->unique()->uuid(),
            'provider' => 'oneup_aad',
            'email' => $this->faker->unique()->safeEmail(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    /**
     * Indicate that the user should be member of one or many groups.
     *
     * @return static
     */
    public function withGroup($count = 1)
    {
        return $this->afterCreating(function (User $user) use ($count) {

            $groups = Group::factory()->count($count)->create();

            foreach ($groups as $group) {
                $user->assignGroup($group->name);
            }
        });
    }

    /**
     * Indicate that the user should have one or many roles.
     *
     * @return static
     */
    public function withRole($count = 1)
    {
        return $this->afterCreating(function (User $user) use ($count) {

            $roles = Role::factory()->count($count)->create();

            foreach ($roles as $role) {
                $user->assignRole($role->name);
            }
        });
    }
}
