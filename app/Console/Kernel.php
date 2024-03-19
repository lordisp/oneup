<?php

namespace App\Console;

use App\Jobs\DismissRiskyUsersScheduler;
use App\Jobs\Pdns\PrivateDnsSync;
use App\Jobs\Scim\ScheduledUserImportJob;
use App\Jobs\ServiceNow\UpdateUsersBasedOnBusinessServiceScheduler;
use App\Jobs\VmStartStopSchedulerJob;
use App\Models\BusinessService;
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

        $this->pruneBatches($schedule)->hourlyAt(0);

        $this->pruneFailed($schedule)->hourlyAt(0);

        $this->pruneTelescope($schedule)
            ->hourlyAt(0)
            ->onOneServer();

        $schedule->job(new PrivateDnsSync)
            ->everyTenMinutes()
            ->onOneServer();

        $schedule->exec('sudo /usr/local/bin/updater.sh')
            ->daily();

        $schedule->job(new VmStartStopSchedulerJob)
            ->everyFifteenMinutes()
            ->onOneServer();

        $schedule->job(new ScheduledUserImportJob)
            ->everyFifteenMinutes()
            ->onOneServer();

        $schedule->job(new DismissRiskyUsersScheduler)
            ->everyFiveMinutes()
            ->onOneServer();

        $schedule->job(new UpdateUsersBasedOnBusinessServiceScheduler(BusinessService::all()))
            ->everyTenMinutes()
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

    protected function pruneTelescope(Schedule $schedule): Event
    {
        return $schedule->command(sprintf("telescope:prune --hours=%s",
            config('services.scheduler.prune-failed.hours')
        ));
    }
}
