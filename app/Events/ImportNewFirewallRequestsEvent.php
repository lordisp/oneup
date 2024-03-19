<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportNewFirewallRequestsEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public PendingBatch $batch)
    {
    }
}
