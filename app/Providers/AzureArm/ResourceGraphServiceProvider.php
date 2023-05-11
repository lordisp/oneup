<?php

namespace App\Providers\AzureArm;

use App\Services\AzureArm\ResourceGraph;
use Illuminate\Support\ServiceProvider;

class ResourceGraphServiceProvider extends ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        $this->app->singleton('resourcegraph', function (){
            return new ResourceGraph();
        });
    }
}
