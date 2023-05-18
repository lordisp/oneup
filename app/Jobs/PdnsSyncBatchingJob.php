<?php

namespace App\Jobs;

use App\Events\StartNewPdnsSynchronization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PdnsSyncBatchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $this->lhg();
        $this->aviatar();
    }

    protected function lhg(): void
    {
        $attributes = [
            'hub' => 'lhg_arm',
            'spoke' => 'lhg_arm',
            'recordType' => ['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT', 'CNAME'],
            'skipZonesForValidation' => [
                'privatelink.postgres.database.azure.com',
                'privatelink.api.azureml.ms',
            ],
        ];
        event(new StartNewPdnsSynchronization($attributes));
    }

    protected function aviatar()
    {
        $attributes = [
            'hub' => 'lhg_arm',
            'spoke' => 'aviatar_arm',
            'recordType' => ['A', 'CNAME'],
            'skipZonesForValidation' => [
                'privatelink.postgres.database.azure.com',
                'privatelink.api.azureml.ms',
            ],
        ];
        event(new StartNewPdnsSynchronization($attributes));
    }
}
