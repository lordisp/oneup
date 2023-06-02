<?php

namespace App\Logger;

use App\Models\LogMessage;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class DatabaseHandler extends AbstractProcessingHandler
{

    public function __construct($level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        LogMessage::create([
            'level' => $record->level->value,
            'level_name' => $record->level->name,
            'message' => $record->message,
            'logged_at' => $record->datetime,
            'context' => $record->context,
            'extra' => $record->extra,
        ]);
    }
}