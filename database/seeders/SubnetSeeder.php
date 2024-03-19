<?php

namespace Database\Seeders;

use App\Models\Subnet;
use Illuminate\Database\Seeder;

class SubnetSeeder extends Seeder
{
    protected array $subnets;

    public function __construct()
    {
        $this->subnets = [
            '10.254.14.128' => '28',
            '10.254.15.0' => '24',
            '10.254.14.0' => '28',
            '10.253.90.0' => '27',
            '10.253.90.32' => '27',
            '10.253.90.128' => '27',
            '10.254.62.160' => '27',
            '10.254.63.96' => '27',
            '10.254.62.192' => '27',
            '10.254.63.128' => '27',
            '10.254.63.160' => '27',
            '10.254.63.192' => '28',
            '10.254.62.0' => '27',
            '10.254.63.0' => '27',
            '10.254.62.32' => '27',
            '10.254.63.32' => '27',
            '10.254.62.64' => '27',
            '10.254.63.64' => '27',
            '10.254.62.96' => '27',
            '10.254.120.32' => '27',
            '10.254.120.16' => '29',
            '10.254.120.64' => '27',
            '10.254.120.8' => '29',
            '10.253.254.0' => '26',
            '10.253.254.64' => '28',
        ];
    }

    public function run(): void
    {
        foreach ($this->subnets as $name => $size) {
            Subnet::firstOrCreate([
                'name' => $name,
                'size' => $size,
                'pci_dss' => now(),
            ]);
        }
    }
}
