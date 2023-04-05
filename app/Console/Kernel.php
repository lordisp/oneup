<?php

namespace App\Console;

use App\Jobs\DnsSyncAviatarJob;
use App\Jobs\DnsSyncJob;
use App\Jobs\Scim\ScheduledUserImportJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

class Kernel extends ConsoleKernel
{

    protected $routeMiddleware = [
        'client' => CheckClientCredentials::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('passport:purge')->hourly();

        $schedule->command('logs:clear', ['--level' => 'debug', '--age' => now()->subHour()->toDateTimeString(),'--job' => true])
            ->hourly()
            ->onOneServer()
            ->runInBackground();

        $schedule->command('logs:clear', ['--level' => 'info', '--age' => now()->subMonth()->toDateTimeString(),'--job' => true])
            ->daily()
            ->onOneServer()
            ->runInBackground();

        $schedule->command('logs:clear', ['--level' => 'error', '--age' => now()->subMonth()->toDateTimeString(),'--job' => true])
            ->daily()
            ->onOneServer()
            ->runInBackground();

        $schedule->job(new DnsSyncJob())
            ->everyTenMinutes()
            ->onOneServer();

        $schedule->job(new DnsSyncAviatarJob())
            ->everyTenMinutes();

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
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
