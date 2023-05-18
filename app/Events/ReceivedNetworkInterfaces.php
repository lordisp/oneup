<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceivedNetworkInterfaces
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(protected array $attributes)
    {
    }

    public function getAttributes($attribute = null): string|array
    {
        return isset($attribute)
        && is_string($attribute)
        && array_key_exists($attribute, $this->attributes)
            ? $this->attributes[$attribute]
            : $this->attributes;
    }

}
