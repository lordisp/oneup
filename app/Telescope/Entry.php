<?php

namespace App\Telescope;

use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;

class Entry extends IncomingEntry
{
    public function isBatch(): bool
    {
        return $this->type === EntryType::BATCH && config('telescope.entries.batch');
    }

    public function isJob(): bool
    {
        return $this->type === EntryType::JOB && config('telescope.entries.job');
    }

    public function isEvent(): bool
    {
        return $this->type === EntryType::EVENT && config('telescope.entries.event');
    }
}