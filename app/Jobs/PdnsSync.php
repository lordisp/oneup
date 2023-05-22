<?php

namespace App\Jobs;

use App\Events\StartNewPdnsSynchronization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PdnsSync implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const HUB = 'lhg_arm';

    protected array $skipZonesForValidation = [
        'privatelink.postgres.database.azure.com',
        'privatelink.westeurope.azmk8s.io',
        'privatelink.api.azureml.ms',
    ];

    public function handle(): void
    {
        $this->lhg();
        $this->aviatar();
    }

    protected function lhg(): void
    {
        $attributes = [
            'hub' => self::HUB,
            'spoke' => 'lhg_arm',
            'recordType' => ['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT', 'CNAME'],
            'skipZonesForValidation' => $this->skipZonesForValidation,
            'skipSubscriptions' => [
                '534fed2b-6945-40c2-bd1b-9bbba36a2f29',
                '10006206-6ed9-41cf-b446-c783f3d71483',
            ]
        ];
        event(new StartNewPdnsSynchronization($attributes));
    }

    protected function aviatar()
    {
        $attributes = [
            'hub' => self::HUB,
            'spoke' => 'aviatar_arm',
            'recordType' => ['A', 'CNAME'],
            'skipZonesForValidation' => $this->skipZonesForValidation,
        ];
        event(new StartNewPdnsSynchronization($attributes));
    }
}
