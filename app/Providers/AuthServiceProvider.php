<?php

namespace App\Providers;

use App\Models\Passport\AuthCode;
use App\Models\Passport\Client;
use App\Models\Passport\PersonalAccessClient;
use App\Models\Passport\Token;
use App\Policies\Admin\TokenCacheProviderPolicy;
use App\Policies\Profile\ClientPolicy;
use App\Policies\ProviderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class => ClientPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerPassport();
        $this->registerPassportScopes();

        $this->registerClientGates();
        $this->registerProviderGates();
    }

    protected function registerPassportScopes()
    {
        Passport::tokensCan([
            'place-orders' => 'Place orders',
            'check-status' => 'Check order status',
        ]);
    }

    protected function registerPassport()
    {
        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }

        /* Hash secrets in database */
        Passport::hashClientSecrets();

        /* Token-lifecycle */
        Passport::tokensExpireIn(now()->addHour());
        Passport::refreshTokensExpireIn(now()->addHour());
        Passport::personalAccessTokensExpireIn(now()->addYears(2));

        /* Overwrite Models */
        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
        Passport::usePersonalAccessClientModel(PersonalAccessClient::class);
    }

    protected function registerClientGates()
    {
        Gate::define('delete-client', [ClientPolicy::class, 'delete']);
    }
    protected function registerProviderGates()
    {
        Gate::define('delete-provider', [TokenCacheProviderPolicy::class, 'delete']);
    }
}
