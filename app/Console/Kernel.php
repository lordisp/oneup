<?php

namespace App\Console;

use App\Jobs\DnsSyncAviatarJob;
use App\Jobs\DnsSyncJob;
use App\Jobs\Scim\ImportUserJob;
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

        $schedule->job(new DnsSyncJob(),'dns')
            ->everyTenMinutes()
            ->onOneServer()
        ;
         $schedule->job(new DnsSyncAviatarJob(),'dns')
             ->everyTenMinutes()
             ->onOneServer()
         ;
         $schedule->job(new ImportUserJob(),'admin')
             ->everyFiveMinutes()
             ->onOneServer()
         ;
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
