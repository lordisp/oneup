<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        exec("sudo /usr/local/bin/updater.sh", $output);

        $this->comment( implode( PHP_EOL, $output ) );
    }
}
