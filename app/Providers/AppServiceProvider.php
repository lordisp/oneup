<?php

namespace App\Providers;

use App\Services\Accessor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Livewire\Component;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Passport::cookie('oneup_token');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->handleHttpSchema();
        $this->registerAccessor();
        $this->registerLivewireMacros();
        $this->registerAzureMacros();
    }

    protected function handleHttpSchema()
    {
        if ($this->app->environment('production', 'stage')) {
            $this->app['request']->server->set('HTTPS', 'on');
            URL::forceScheme('https');
        }
    }

    protected function registerAccessor()
    {
        if (class_exists(Accessor::class)) $this->app->singleton('accessor', function () {
            return new Accessor();
        });
    }

    protected function registerLivewireMacros()
    {
        /**
         * Use this macro for toaster notification on the same page
         * $this->event('my event, 'success') ['success', 'warning', 'error']
         */
        Component::macro('event', function ($message, $type = 'info') {
            $this->dispatchBrowserEvent('notify', ['message' => $message, 'type' => $type]);
        });

        /**
         * Use this macro for toaster notification before a redirect to another page
         * $this->flash('my event, 'success') ['success', 'warning', 'error']
         */
        Component::macro('flash', function ($message, $type = 'info') {
            session()->flash('notify', ['message' => $message, 'type' => $type]);
        });

        Builder::macro('search', function ($field, $string) {
            return $string ? $this->orWhere($field, 'like', '%'.$string.'%') : $this;
        });
    }
    protected function registerAzureMacros()
    {
        Http::macro('azure', function () {
            return Http::baseUrl('https://management.azure.com');
        });
        Http::macro('graph', function () {
            return Http::baseUrl('https://graph.microsoft.com');
        });
    }
}
