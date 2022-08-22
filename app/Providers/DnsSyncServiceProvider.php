<?php

namespace App\Providers;

use App\Services\DnsSync;
use Illuminate\Support\ServiceProvider;

class DnsSyncServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->singleton('dnssync', function (){
            return new DnsSync();
        });
    }
}
