<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DnsSyncZone>
 */
class DnsSyncZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $zones = array_map('trim', file(database_path().'/factories/dns_zones.stup'));

        shuffle($zones);

        return [
            'name' => $zones[0],
        ];
    }
}
