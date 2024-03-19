<?php

namespace App\Jobs\Logger;

use App\Logger\DatabaseCleaner;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DatabaseCleanerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ?string $level, protected ?Carbon $age)
    {
    }

    public function handle(): void
    {
        DatabaseCleaner::forceDelete()
            ->when($this->level, fn ($query) => $query->level($this->level))
            ->when($this->age, fn ($query) => $query->age($this->age))
            ->run();
    }
}
