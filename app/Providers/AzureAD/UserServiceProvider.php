<?php

namespace App\Providers\AzureAD;

use App\Services\AzureAD\User;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
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
        $this->app->singleton('user', function (){
            return new User;
        });
    }
}
