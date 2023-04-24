<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\Pdns\Pdns sync()
 * @method static \App\Services\Pdns\Pdns withSpoke(string $provider)
 * @method static \App\Services\Pdns\Pdns withHub(string $provider, string $subscriptionId, string $resourceGroup)
 * @method static \App\Services\Pdns\Pdns withRecordType(string|array $type)
 * @method static \App\Services\Pdns\Pdns withZones(string|array $zones)
 * @see \App\Services\Pnds
 */
class Pdns extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return 'pdns';
    }
}