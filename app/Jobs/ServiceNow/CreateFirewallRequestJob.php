<?php

namespace App\Jobs\ServiceNow;

use App\Models\User;
use App\Services\ServiceNow\CreateFirewallRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFirewallRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected string $ruleId, protected User $user)
    {
    }

    public function handle(): void
    {
        CreateFirewallRequest::process($this->ruleId, $this->user);
    }
}
