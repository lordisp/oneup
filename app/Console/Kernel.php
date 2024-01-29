<?php

namespace App\Console;

use App\Jobs\PdnsSync;
use App\Jobs\Scim\ScheduledUserImportJob;
use App\Jobs\VmStartStopSchedulerJob;
use Illuminate\Console\Scheduling\Event;
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
        $schedule->command('passport:purge')->hourlyAt(0);

        $schedule->command('cache:prune-stale-tags')->hourlyAt(0);

        $schedule->command(sprintf("logs:clear --level debug --age %s --job", now()->subHour()->toDateTimeString()))
            ->hourlyAt(0)
            ->onOneServer()
            ->runInBackground();

        $schedule->command(sprintf("logs:clear --level info --age %s --job", now()->subMonth()->toDateTimeString()))
            ->dailyAt('00:00')
            ->onOneServer()
            ->runInBackground();

        $schedule->command(sprintf("logs:clear --level error --age %s --job", now()->subMonth()->toDateTimeString()))
            ->dailyAt('00:00')
            ->onOneServer()
            ->runInBackground();

        $this->pruneBatches($schedule)->hourlyAt(0);

        $this->pruneFailed($schedule)->hourlyAt(0);

        $this->pruneTelescope($schedule)
            ->hourlyAt(0)
            ->onOneServer();

        $schedule->job(new PdnsSync())
            ->everyTenMinutes()
            ->onOneServer();

        $schedule->exec('sudo /usr/local/bin/updater.sh')
            ->daily();

        $schedule->job(new VmStartStopSchedulerJob)
            ->everyFifteenMinutes()
            ->onOneServer();

        $schedule->job(new ScheduledUserImportJob())
            ->everyFifteenMinutes()
            ->onOneServer();

//        $schedule->job(new DismissRiskyUsersScheduler)
//            ->everyFiveMinutes()
//            ->onOneServer();
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

    protected function pruneTelescope(Schedule $schedule): Event
    {
        return $schedule->command(sprintf("telescope:prune --hours=%s",
            config('services.scheduler.prune-failed.hours')
        ));

    }
}
