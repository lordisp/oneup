<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InterfacesReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(protected string $provider)
    {
    }

    public function getProvider(): string
    {
        return $this->provider;
    }
}
