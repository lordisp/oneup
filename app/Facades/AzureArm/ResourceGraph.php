<?php

namespace App\Facades\AzureArm;

use Illuminate\Support\Facades\Facade;

/**
 * @method static type(string $resourceType, string $operator = '==')
 * @method static withToken($token)
 * @method static withProvider(string $string)
 * @method static withSubscription(string $subscriptionId)
 * @method static toCache(string $name)
 * @method static fromCache(string $name)
 * @method static deleteCache(string $name)
 */
class ResourceGraph extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'resourcegraph';
    }
}
