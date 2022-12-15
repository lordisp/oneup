<?php

namespace App\Notifications;

use App\Models\FirewallRule;
use App\Models\ServiceNowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class FirewallReviewRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    const MONTHS = 7;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $firstName = $notifiable->firstName;
        return (new MailMessage)
            ->greeting("Hello {$firstName}!")
            ->line("You have {$this->rulesCount($notifiable)} Firewall-Rules to review!")
            ->line('- Go to OneUp and review at least any PCI-Related rules.')
            ->line('- Decide whether you want to "keep" or "decommission" a given rule.')
            ->action('Review Now', url(route('firewall.requests.read')))
            ->line('We\'ll get back to you as soon as we find new reviews for you.')
            ->line('If you have any question, dont hesitate to contact us.');
    }

    public function toDatabase($notifiable): array
    {
        $rulesCount = $this->rulesCount($notifiable);

        return [
            'title' => 'Import Firewall-Requests',
            'message' => "You have {$rulesCount} Firewall-Rules to review",
        ];
    }

    protected function rulesCount($notifiable)
    {
        return FirewallRule::select(['id', 'action', 'pci_dss', 'last_review'])
            ->whereIn('service_now_request_id', ServiceNowRequest::query()
                ->where('requestor_mail', $notifiable->email)
                ->pluck('id'))
            ->review()->get()->count();
    }
}
