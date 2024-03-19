<?php

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [


    'aliases' => Facade::defaultAliases()->merge([
        // 'ExampleClass' => App\Example\ExampleClass::class,
        'TokenCache' => App\Facades\TokenCache::class,
        'User' => App\Facades\AzureAD\User::class,
        'resourcegraph' => App\Facades\AzureArm\ResourceGraph::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
    ])->toArray(),

];
