<?php

namespace App\Logger;

use Illuminate\Support\Str;
use Monolog\Logger;

class DatabaseLogger
{
    private string $level;

    public function __construct()
    {
        $this->level = Str::title(config('logging.channels.db.level'));
    }

    public function __invoke(array $config): Logger
    {
        return new Logger('Database', [
            new DatabaseHandler($this->level),
        ]);
    }
}
