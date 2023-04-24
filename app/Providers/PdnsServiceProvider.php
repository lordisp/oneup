<?php

namespace App\Providers;

use App\Services\Pdns\Pdns;
use Illuminate\Support\ServiceProvider;

class PdnsServiceProvider extends ServiceProvider
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
        $this->app->singleton('pdns', function () {
            return new Pdns();
        });
    }
}
