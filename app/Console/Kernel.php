<?php

namespace App\Console;

use App\Jobs\PdnsSync;
use App\Jobs\Scim\ScheduledUserImportJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

class Kernel extends ConsoleKernel
{

    protected array $routeMiddleware = [
        'client' => CheckClientCredentials::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('passport:purge')->hourly();

        $schedule->command('cache:prune-stale-tags')->hourly();

        $schedule->command(sprintf("logs:clear --level debug --age %s --job", now()->subHour()->toDateTimeString()))
            ->hourly()
            ->onOneServer()
            ->runInBackground();

        $schedule->command(sprintf("logs:clear --level info --age %s --job", now()->subMonth()->toDateTimeString()))
            ->daily()
            ->onOneServer()
            ->runInBackground();

        $schedule->command(sprintf("logs:clear --level error --age %s --job", now()->subMonth()->toDateTimeString()))
            ->daily()
            ->onOneServer()
            ->runInBackground();

        $this->pruneBatches($schedule)->hourly();

        $this->pruneFailed($schedule)->hourly();

        $schedule->job(new PdnsSync())
            ->everyTenMinutes()
            ->onOneServer();

        $schedule->exec('sudo /usr/local/bin/updater.sh')
            ->daily();

        $schedule->job(new ScheduledUserImportJob())
            ->everyFifteenMinutes()
            ->onOneServer();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function pruneBatches($schedule)
    {
        return $schedule->command(sprintf("queue:prune-batches --hours=%s --cancelled=%s --unfinished=%s",
            config('services.scheduler.prune-batches.hours'),
            config('services.scheduler.prune-batches.cancelled'),
            config('services.scheduler.prune-batches.unfinished')
        ));
    }

    protected function pruneFailed($schedule)
    {
        return $schedule->command(sprintf("queue:prune-failed --hours=%s",
            config('services.scheduler.prune-failed.hours')
        ));
    }
}
