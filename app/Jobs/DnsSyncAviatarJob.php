<?php

namespace App\Jobs;

use App\Facades\DnsSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DnsSyncAviatarJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Log::info('Initiate DNS Sync for aviatar_arm');
        DnsSync::withHub('lhg_arm',config('dnssync.subscription_id'),config('dnssync.resource_group'))
            ->withSpoke('aviatar_arm')
            ->withRecordType(['A'])
            ->start();
    }
}
