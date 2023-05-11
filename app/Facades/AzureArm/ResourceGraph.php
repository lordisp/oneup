<?php

namespace App\Facades\AzureArm;

use Illuminate\Support\Facades\Facade;

/**
 * @method static cache()
 * @method static withProvider(string $string)
 * @method static withSubscription(string $subscriptionId)
 * @method static type(string $resourceType, string $operator = '==')
 * @method static fromCache(string $provider = 'lhg_arm')
 */
class ResourceGraph extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'resourcegraph';
    }
}