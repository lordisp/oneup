<?php

namespace App\Providers;

use App\Models\Passport\AuthCode;
use App\Models\Passport\Client;
use App\Models\Passport\PersonalAccessClient;
use App\Models\Passport\Token;
use App\Models\Role;
use App\Policies\Admin\GroupPolicy;
use App\Policies\Admin\OperationPolicy;
use App\Policies\Admin\RolesPolicy;
use App\Policies\Admin\TokenCacheProviderPolicy;
use App\Policies\Profile\ClientPolicy;
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
        Client::class => ClientPolicy::class,
        Role::class => RolesPolicy::class
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
        $this->registerProviderPolicy();
        $this->registerRbacGates();
        $this->registerRolePolicy();
        $this->registerOperationPolicy();
        $this->registerGroupPolicy();
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

    protected function registerProviderPolicy()
    {
        Gate::define('provider-readAll', [TokenCacheProviderPolicy::class, 'viewAny']);
        Gate::define('provider-read', [TokenCacheProviderPolicy::class, 'view']);
        Gate::define('provider-delete', [TokenCacheProviderPolicy::class, 'delete']);
        Gate::define('provider-update', [TokenCacheProviderPolicy::class, 'update']);
        Gate::define('provider-create', [TokenCacheProviderPolicy::class, 'create']);
    }

    protected function registerRolePolicy()
    {
        Gate::define('roles-readAll', [RolesPolicy::class, 'viewAny']);
        Gate::define('roles-read', [RolesPolicy::class, 'view']);
        Gate::define('roles-delete', [RolesPolicy::class, 'delete']);
        Gate::define('roles-update', [RolesPolicy::class, 'update']);
        Gate::define('roles-create', [RolesPolicy::class, 'create']);
    }

    protected function registerOperationPolicy()
    {
        Gate::define('operation-readAll', [OperationPolicy::class, 'viewAny']);
        Gate::define('operation-read', [OperationPolicy::class, 'view']);
        Gate::define('operation-delete', [OperationPolicy::class, 'delete']);
        Gate::define('operation-update', [OperationPolicy::class, 'update']);
        Gate::define('operation-create', [OperationPolicy::class, 'create']);
    }

    protected function registerGroupPolicy()
    {
        Gate::define('group-readAll', [GroupPolicy::class, 'viewAny']);
        Gate::define('group-read', [GroupPolicy::class, 'view']);
        Gate::define('group-delete', [GroupPolicy::class, 'delete']);
        Gate::define('group-detach-members', [GroupPolicy::class, 'detachMembers']);
        Gate::define('group-attach-members', [GroupPolicy::class, 'attachMembers']);
        Gate::define('group-detach-roles', [GroupPolicy::class, 'detachRoles']);
        Gate::define('group-attach-roles', [GroupPolicy::class, 'attachRoles']);
        Gate::define('group-detach-owners', [GroupPolicy::class, 'detachOwners']);
        Gate::define('group-attach-owners', [GroupPolicy::class, 'attachOwners']);
        Gate::define('group-update', [GroupPolicy::class, 'update']);
        Gate::define('group-create', [GroupPolicy::class, 'create']);
    }

    protected
    function registerRbacGates()
    {
        Gate::after(function ($user, $operation) {
            return $user->operations()->contains($operation);
        });
    }
}
