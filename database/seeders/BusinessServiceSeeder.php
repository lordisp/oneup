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
        'LHG_SOAR_P',
        'LHG_MISP_P',
        'LHG_CDC_P',
        'LHG_SIEM_P',
        'LHG_LHGROUPAD_P',
        'LHG_PKIUSERCERTSYNC_P',
        'LHG_PASSWORDSYNC_P',
        'LHG_DIRXML_P',
        'LHG_MFACDS_P',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->businessServices as $businessService) {
            BusinessService::firstOrCreate(['name' => $businessService], ['name' => $businessService]);
        }
    }
}
