<?php

namespace App\Services\FirewallRequests;

use App\Models\FirewallRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RuleStatus extends Rule
{

    public static function reset($rule): FirewallRule
    {
        return (new self($rule))
            ->resetStatus()
            ->rule;
    }

    private function resetStatus(): static
    {
        if ($this->rule->last_review <= now()->subQuarter()
            && $this->rule->pci_dss
            && $this->rule->action === 'add'
            && $this->rule->status !== 'deleted'
            && $this->rule->status !== 'open'
            && Carbon::parse($this->rule->end_date) <= now()
        ) {
            $this->rule->status = 'open';
            $this->rule->save();
            Log::debug("UPDATED STATUS: reset status from '{$this->rule->status}' to 'open'");
        }
        return $this;
    }
}