<?php

namespace Database\Seeders;

use App\Models\DnsSyncZone;
use Illuminate\Database\Seeder;

class DnsSyncZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */


    public function run(): void
    {
        $zones = array_map('trim', file(database_path() . '/factories/dns_zones.stup'));

        foreach ($zones as $zone) {
            DnsSyncZone::factory()->state(['name' => $zone])->create();
        }

    }
}
