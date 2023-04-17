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
        $due = now()->addWeeks(2)->weekday()->format('d.m.Y');
        return (new MailMessage)
            ->subject('Quarterly Review of PCI-Related Firewall Rules')
            ->line("*You have {$this->numberOfRules($notifiable)} firewall rules to review!*")
            ->greeting("Dear {$firstName}!")
            ->line('We would like to inform you that you are a participant in one or more business services that are directly related to PCI-related firewall rules. For this reason, these rules must be reviewed on a quarterly basis.')
            ->line('This is essential to ensure security and compliance with PCI guidelines. Please take the time to ensure that the firewall rules meet the requirements and are updated if necessary.')
            ->line('We strongly recommend that you coordinate with the original requester of the firewall rule or the other participants of the business services to ensure that the review can be conducted smoothly. Additionally, it is essential that you adhere to the timeline for these reviews to avoid any compliance issues.')
            ->line('Please note that all other participants of the business services have also received this notification.')
            ->action('Review Now', url(route('firewall.requests.read')))
            ->line("Please ensure that you have completed this task no later than **{$due}**.")
            ->line('If you have any question, don\'t hesitate to contact us.');
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