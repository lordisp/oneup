<?php

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Laravel Framework Service Providers...
         */

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\TelescopeServiceProvider::class,
        \Barryvdh\Debugbar\ServiceProvider::class,
        App\Providers\TokenCacheServiceProvider::class,
        App\Providers\AzureAD\UserServiceProvider::class,
        App\Providers\AzureArm\ResourceGraphServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        // 'ExampleClass' => App\Example\ExampleClass::class,
        'TokenCache' => App\Facades\TokenCache::class,
        'User' => App\Facades\AzureAD\User::class,
        'resourcegraph' => App\Facades\AzureArm\ResourceGraph::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
