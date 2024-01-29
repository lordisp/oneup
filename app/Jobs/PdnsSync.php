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

    const CHUNK = 10;
    protected array $jobs = [
        LhgTenantJob::class,
        AviatarTenantJob::class
    ];

    public function handle(): void
    {
        $jobs = $this->getJobs();

        if (empty($jobs)) {
            return;
        }

        if (count($jobs) > self::CHUNK) {
            $jobs = array_chunk($jobs, self::CHUNK);
        }

        Bus::batch([$jobs])
            ->onQueue(config('dnssync.queue_name'))
            ->name('pdns')
            ->dispatch();
    }

    protected function getJobs(): array
    {
        foreach ($this->jobs as $job) {
            $jobs[] = new $job;
        }

        return $jobs ?? [];
    }
}
