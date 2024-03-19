<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    App\Providers\TokenCacheServiceProvider::class,
    App\Providers\AzureAD\UserServiceProvider::class,
    App\Providers\AzureArm\ResourceGraphServiceProvider::class,
];
