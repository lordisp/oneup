<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreateFirewallRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private mixed $body;

    public function __construct(protected ClientResponse|HttpResponse $response)
    {
        if ($response->status() >= 200 && $response->status() < 300) {
            $this->body = $response->json('result');
        }
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        if ($this->response->status() >= 500||$this->response->status() >= 400 && $this->response->status() < 500) {
            return (new MailMessage)
                ->greeting("Hello {$notifiable->firstName}!")
                ->line('Your request was saved in OneUp, but we could not reach Service-Now to forward the dismantling. Therefore, we ask you to manually order the dismantling of the rule in Service-Now.')
                ->action('Decommissioned Rules', url(route('firewall.requests.read', ['filters' => ['own' => '1', 'status' => 'delete']])))
                ->action('Firewall-Request Form', 'https://lhgroup.service-now.com/sp?id=sc_cat_item&sys_id=960ac540db10eb00fe5d9785ca96191e')
                ->line('If you have any question, dont hesitate to contact us.');
        }

        return (new MailMessage)
            ->greeting("Hello {$notifiable->firstName}!")
            ->line("Your request has been successfully submitted to Service-Now. You will be informed about the further progress via Service-Now under reference {$this->body['requestNumber']}.")
            ->action('Service-Now', url('https://lhgroup.service-now.com/sp'))
            ->line('If you have any question, dont hesitate to contact us.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
