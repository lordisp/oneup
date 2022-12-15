<?php

namespace App\Jobs;

use App\Models\ServiceNowRequest;
use App\Models\User;
use App\Notifications\FirewallReviewRequiredNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class InviteFirewallReviewerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    /**
     * Send Users a Notification if they have Firewall-Rules under PCI-Scope, which last review is
     * @return void
     */
    public function handle()
    {

        $users = User::whereIn('email', ServiceNowRequest::whereRelation('rules', function ($query) {
            $query->review();
        })->pluck('requestor_mail'))
            ->get();

        Notification::send($users, new FirewallReviewRequiredNotification());

    }
}