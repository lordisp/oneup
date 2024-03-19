<?php

namespace App\Notifications;

use Carbon\CarbonInterval;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class FirewallRequestsImportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $event)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $duration = (string) CarbonInterval::make($this->event['createdAt']->diff($this->event['finishedAt']));
        $firstName = Str::title($notifiable->firstName);

        return (new MailMessage)
            ->greeting("Hello {$firstName}!")
            ->line("{$this->event['totalJobs']} Firewall-Requests have been imported or updated in {$duration}.")
            ->action('Review Now', url(route('firewall.requests.read')))
            ->tag('oneup');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Import Firewall-Requests',
            'message' => "{$this->event['totalJobs']} Firewall-Requests have been imported or updated",
        ];
    }
}
