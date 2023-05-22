<?php

namespace App\Providers\App\Listeners;

use App\Jobs\Pdns\UpdateRecordJob;
use App\Providers\App\Events\UpdateRecordEvent;
use Illuminate\Contracts\Queue\ShouldQueue;


class UpdateRecord implements ShouldQueue
{
    public function handle(UpdateRecordEvent $event)
    {
        UpdateRecordJob::dispatch($event->attributes);
    }
}