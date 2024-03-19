<?php

namespace App\Providers\AzureAD;

use App\Services\AzureAD\User;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->singleton('user', function () {
            return new User;
        });
    }
}
