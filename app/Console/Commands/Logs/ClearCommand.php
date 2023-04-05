<?php

namespace App\Console\Commands\Logs;

use App\Jobs\Logger\DatabaseCleanerJob;
use App\Models\LogMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ClearCommand extends Command
{
    protected $signature = 'logs:clear
                            {--level= : The name of the log level (info, debug, error,...)} 
                            {--age= : A date in the past [10.12.2021]}
                            {--job : Run the operation in the background. }';

    protected $description = 'Clear logs from database';

    protected string|null $level;

    protected Carbon|null $age;

    public function handle(): void
    {
        $this->normalizeArguments();

        if ($this->option('job')) {
            DatabaseCleanerJob::dispatch($this->level, $this->age)->afterCommit();
            return;
        }

        $this->line("Deleting {$this->level} messages...");

        $logMessage = $this->withProgressBar(LogMessage::query()
            ->withTrashed()
            ->when($this->level, fn($query) => $query->where('level_name', '=', $this->level))
            ->when($this->age, fn($query) => $query->where('deleted_at', '<', $this->age))
            ->get(), function (LogMessage $logMessage) {
            $logMessage->forceDelete();
        });

        $this->newLine();

        if ($logMessage->count() > 0) {
            $this->info('DONE!');
        } else {
            $this->line('No logs found!');
        }
    }

    private function normalizeArguments()
    {
        $this->level = $this->option('level')?Str::upper($this->option('level')):null;
        $this->age = $this->option('age')?Carbon::parse($this->option('age')):now();
    }
}
