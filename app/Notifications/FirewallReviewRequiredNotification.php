<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class FirewallReviewRequiredNotification extends Notification implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    private $user;
    private string $uniqueId;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    public function retryUntil(): Carbon
    {
        return now()->addMinutes(20);
    }

    public function uniqueId(): string
    {
        return $this->uniqueId;
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
            ->line("You have {$this->numberOfRules($notifiable)} Firewall-Rules to review!")
            ->line('- Go to OneUp and review at least any PCI-Related rules.')
            ->line('- Decide whether you want to `keep` or `decommission` a given rule.')
            ->action('Review Now', url(route('firewall.requests.read')))
            ->line('We\'ll get back to you as soon as we find new reviews for you.')
            ->line('If you have any question, dont hesitate to contact us.');
    }

    public function toDatabase($notifiable): array
    {
        $numberOfRules = $this->numberOfRules($notifiable);

        return [
            'title' => 'Review your Firewall-Rules',
            'message' => "You have {$numberOfRules} Firewall-Rules to review",
        ];
    }

    protected function numberOfRules($notifiable)
    {
        return $notifiable->BusinessServices->map->rules
            ->map(function ($query) {
                return $query
                    ->where('action', '=', 'add')
                    ->where('pci_dss', true)
                    ->where('end_date', '>', now())
                    ->where('status', '!=', 'deleted')
                    ->where(function ($rule) {
                        return $rule->last_review === null
                            || $rule->last_review < now()->subQuarter();
                    });
            })
            ->flatten()
            ->count();
    }
}