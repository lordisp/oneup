<?php

namespace App\Providers;

use App\Services\Accessor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Livewire\Component;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::cookie('oneup_token');
        Passport::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //$this->handleHeaders();
        $this->handleHttpSchema();
        $this->registerAccessor();
        $this->registerLivewireMacros();
        $this->registerAzureMacros();

        $this->bootRoute();
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
        if (class_exists(Accessor::class)) {
            $this->app->singleton('accessor', function () {
                return new Accessor();
            });
        }
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

    protected function handleHeaders(): void
    {
        header_remove('X-Powered-By');
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'sameorigin',
            //'Content-Security-Policy' => "default-src 'self'; style-src 'self' {$url}",
            //'Content-Security-Policy' => "script-src 'nonce-".Vite::cspNonce()."'",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
            'Referrer-Policy' => 'no-referrer-when-downgrade',
        ];

        foreach ($headers as $header => $value) {
            header($header.': '.$value);
        }
    }

    public function bootRoute()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('rates.api'))->by($request->user()?->id ?: $request->ip());
        });


    }
}
