<?php

namespace Database\Seeders;

use App\Models\BusinessService;
use Illuminate\Database\Seeder;

class BusinessServiceSeeder extends Seeder
{
    private array $businessServices = [
        'LHG_AZUREFOUNDATION_P',
        'LHG_GOVERNOR_P',
        'LHG_PAM_P',
        'LHG_CDC_P',
        'LHG_SIEM_P',
        'LHG_LHGROUPAD_P',
        'LHG_MFACDS_P',
        'LHG_PASSWORDSYNC_P',
        'LHG_LHGROUPAD_P',
        'LHG_GOVMEMLHGROUPAD_P',
        'LHG_PASSWORDSYNC_P',
        'LHG_PASSWORDSYNCCHECK_P',
        'LHG_CDSPASSWORDCHANGE_P',
        'LHG_MIM_P',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->businessServices as $businessService) {
            BusinessService::firstOrCreate(['name' => $businessService], ['name' => $businessService]);
        }
    }
}
