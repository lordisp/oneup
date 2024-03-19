<?php

namespace Database\Seeders;

use App\Models\DnsSyncZone;
use Illuminate\Database\Seeder;

class DnsSyncAllZonesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = array_map('trim', file(database_path().'/factories/dns_zones.stup'));

        foreach ($zones as $zone) {
            DnsSyncZone::factory()->state(['name' => $zone])->create();
        }

    }
}
