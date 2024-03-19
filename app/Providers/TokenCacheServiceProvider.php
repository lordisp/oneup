<?php

namespace App\Providers;

use App\Services\TokenCache;
use Illuminate\Support\ServiceProvider;

class TokenCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->singleton('tokencache', function () {
            return new TokenCache();
        });

        /* Uncomment the below line to disable encryption */
        // \App\Facades\TokenCache::withoutEncryption();
    }
}
