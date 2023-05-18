<?php

namespace App\Events;

use App\Models\TokenCacheProvider;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StartNewPdnsSynchronization
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(protected array $attributes)
    {
        $provider = TokenCacheProvider::get('name')->map(fn($query) => $query->name)->toArray();

        Validator::validate($this->attributes, [
            'hub' => ['required', 'string', Rule::in($provider)],
            'spoke' => ['required', 'string', Rule::in($provider)],
            'recordType' => 'required|array',
            'recordType.*' => Rule::in(['A', 'AAAA', 'MX', 'PTR', 'SRV', 'TXT', 'CNAME'])
        ],
            ['recordType.*' => 'Invalid record-type']);
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
