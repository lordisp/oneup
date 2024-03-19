<?php

namespace Database\Factories;

use App\Models\BusinessService;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirewallRuleFactory extends Factory
{
    public function definition(): array
    {
        $noExpiry = $this->faker->boolean(70); // 70% chance to be true
        $wellKnownPorts = [20, 21, 22, 23, 25, 53, 80, 110, 443]; // Example well-known ports
        $created_at = $this->faker->dateTimeBetween('-1 year', 'now');
        $updated_at = $this->faker->dateTimeBetween($created_at, 'now');

        return [
            'action' => $this->faker->boolean(70) ? 'add' : 'delete',
            'type_destination' => 'ip_address_dest',
            'destination' => json_encode($this->faker->randomElements($this->generateIpAddresses(), rand(1, 10))),
            'type_source' => 'ip_address_source',
            'source' => json_encode($this->faker->randomElements($this->generateIpAddresses(true), rand(1, 10))),
            'service' => $this->faker->boolean(70) ? 'tcp' : 'udp',
            'destination_port' => json_encode($this->faker->randomElements($wellKnownPorts, rand(1, 2))),
            'description' => $this->faker->sentence(),
            'no_expiry' => $noExpiry ? 'Yes' : 'No',
            'end_date' => $noExpiry ? null : $this->faker->dateTimeBetween('+1 year', '+5 years')->format('Y-m-d\TH:i:s.000000\Z'),
            'pci_dss' => $this->faker->boolean(),
            'nat_required' => $this->faker->randomElement(['Yes', 'No']),
            'application_id' => $this->faker->uuid(),
            'contact' => $this->faker->email(),
            'business_purpose' => $this->faker->text(),
            'status' => $this->faker->word(),
            'last_review' => null,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
            'hash' => $this->faker->unique()->md5(),

            'service_now_request_id' => $this->faker->unique()->uuid(),
            'business_service_id' => BusinessService::factory(),
        ];
    }

    private function generateIPs(): array
    {
        $count = $this->faker->numberBetween(1, 5);

        $IPs = [];
        for ($i = 0; $i < $count; $i++) {
            $IPs[] = $this->faker->ipv4();
        }

        return $IPs;
    }

    private function generateIpAddresses($privateOnly = false, $count = 10)
    {
        $ips = [];
        for ($i = 0; $i < $count; $i++) {
            if ($privateOnly) {
                $ips[] = $this->faker->localIpv4();
            } else {
                // Randomly choose between private and public IPs
                $ips[] = $this->faker->boolean() ? $this->faker->localIpv4() : $this->faker->ipv4();
            }
        }

        return $ips;
    }
}
