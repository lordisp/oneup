<?php

namespace App\Jobs;

use App\Jobs\Pdns\AviatarTenantJob;
use App\Jobs\Pdns\LhgTenantJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class PdnsSync implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle(): void
    {
        Bus::batch([
            [
                new LhgTenantJob
            ],
            [
                new AviatarTenantJob
            ],
        ])
            ->name('pdns')
            ->dispatch();
    }


}
