<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\DnsSync start()
 * @method static \App\Services\DnsSync withSpoke(string $provider)
 * @method static \App\Services\DnsSync withHub(string $provider, string $subscriptionId, string $resourceGroup)
 * @method static \App\Services\DnsSync withRecordType(string|array $type)
 * @see \App\Services\DnsSync
 */
class DnsSync extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return 'dnssync';
    }
}