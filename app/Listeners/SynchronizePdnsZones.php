<?php

namespace App\Listeners;

use App\Events\ReceivedNetworkInterfaces;
use App\Exceptions\DnsZonesException;
use App\Services\Pdns\Pdns;
use App\Validators\PdnsValidate;
use DateTime;
use Illuminate\Contracts\Queue\ShouldQueue;

class SynchronizePdnsZones implements ShouldQueue
{
    protected string $resourceGroup;
    protected string $subscriptionId;

    public function viaConnection(): string
    {
        return config('app.env') === 'testing' ? 'sync' : 'redis';
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }

    public function __construct()
    {
        $this->subscriptionId = config('dnssync.subscription_id');
        $this->resourceGroup = config('dnssync.resource_group');
    }

    /**
     * @throws DnsZonesException
     */
    public function handle(ReceivedNetworkInterfaces $event): void
    {
        $pdns = $this->init($event);

        $pdns->sync($event->getAttributes('resources'));
    }

    protected function init(ReceivedNetworkInterfaces $event): Pdns
    {
        $pdns = new Pdns();

        if (array_key_exists('recordType', $event->getAttributes())) {
            $pdns->withRecordType(PdnsValidate::recordType($event->getAttributes('recordType')));
        }

        if (array_key_exists('hub', $event->getAttributes())) {
            $pdns->withHub($event->getAttributes('hub'), $this->subscriptionId, $this->resourceGroup);
        }

        if (array_key_exists('spoke', $event->getAttributes())) {
            $pdns->withSpoke($event->getAttributes('spoke'));
        }

        if (array_key_exists('withZones', $event->getAttributes())) {
            $pdns->withZones($event->getAttributes('withZones'));
        }

        if (array_key_exists('withSubscriptions', $event->getAttributes())) {
            $pdns->withSubscriptions($event->getAttributes('withSubscriptions'));
        }

        if (array_key_exists('skipSubscriptions', $event->getAttributes())) {
            $pdns->skipSubscriptions($event->getAttributes('skipSubscriptions'));
        }

        if (array_key_exists('skipZonesForValidation', $event->getAttributes())) {
            $pdns->skipZonesForValidation($event->getAttributes('skipZonesForValidation'));
        }

        return $pdns;
    }
}
