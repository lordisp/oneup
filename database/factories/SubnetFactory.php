<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SubnetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->getFakeNetwork(),
            'size' => $this->faker->numberBetween(1, 32),
            'pci_dss' => Carbon::now(),
        ];
    }

    protected function getFakeNetwork(): string
    {
        $ip_address = $this->faker->localIpv4();
        $ip_parts = explode('.', $ip_address);
        $ip_parts[3] = '0';

        return implode('.', $ip_parts);
    }
}
