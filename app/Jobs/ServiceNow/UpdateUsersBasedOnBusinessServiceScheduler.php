<?php

namespace App\Jobs\ServiceNow;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UpdateUsersBasedOnBusinessServiceScheduler implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Collection $businessServices)
    {

    }

    public function handle(): void
    {
        foreach ($this->businessServices as $businessService) {
            UpdateUsersBasedOnBusinessServiceJob::dispatch($businessService);
        }
    }
}
