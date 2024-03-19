<?php

namespace App\Jobs\ServiceNow;

use App\Models\FirewallRule;
use App\Models\User;
use App\Services\ServiceNow\CreateFirewallRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateFirewallRequestJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected FirewallRule $rule, protected User $user)
    {
    }

    public function handle(): void
    {
        CreateFirewallRequest::process($this->rule, $this->user);
    }
}
