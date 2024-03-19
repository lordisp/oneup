<?php

namespace App\Jobs;

use App\Models\FirewallRule;
use App\Notifications\FirewallReviewRequiredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteFirewallReviewerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    /**
     * Send Users a Notification if they have Firewall-Rules under PCI-Scope,
     * which last review is behind a quarter
     */
    public function handle(): void
    {
        foreach ($this->getPCIRulesReviewers() as $reviewer) {
            Notification::send(
                $reviewer,
                new FirewallReviewRequiredNotification(Str::random(40))
            );
        }
    }

    private function getPCIRulesReviewers(): Collection
    {
        return FirewallRule::query()
            ->review()
            ->get()
            ->map(fn ($query) => $query->businessService->users()
                ->whereStatus(1)
                ->select(['id', 'email'])
                ->get()
            )
            ->flatten()
            ->unique('id');
    }
}
