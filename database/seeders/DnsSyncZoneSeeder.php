<?php

namespace Database\Seeders;

use App\Models\DnsSyncZone;
use Illuminate\Database\Seeder;

class DnsSyncZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = array_map('trim', file(database_path().'/factories/dns_zone.stup'));

        foreach ($zones as $zone) {
            DnsSyncZone::factory()->state(['name' => $zone])->create();
        }

    }
}
