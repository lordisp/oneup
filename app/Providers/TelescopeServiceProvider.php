<?php

namespace App\Providers;

use App\Models\Operation;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->type == EntryType::BATCH && config('telescope.entries.batch') ||
                $entry->type == EntryType::JOB && config('telescope.entries.job') ||
                $entry->type == EntryType::EVENT && config('telescope.entries.event') ||
                $entry->type == EntryType::CACHE && config('telescope.entries.cache') ||
                $entry->type == EntryType::QUERY && config('telescope.entries.query') ||
                $entry->type == EntryType::REDIS && config('telescope.entries.redis') ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            return $user->operations()->contains(
                cache()->tags('rbac')->remember('viewTelescope', 1440, function () {
                    return Operation::updateOrCreate([
                        'operation' => 'admin/telescope/view',
                        'description' => 'Can import firewall-requests from Service-Now',
                    ])->operation;
                })
            );
        });
    }
}
