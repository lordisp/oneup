<?php

namespace App\Logger;

use App\Models\LogMessage;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\HigherOrderWhenProxy;
use InvalidArgumentException;

class DatabaseCleaner
{
    const levels = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG', 'API',];

    protected array|null $level = null;

    protected Carbon|null $age = null;

    public static function forceDelete(): static
    {
        return new static();
    }

    public function level(string $level): static
    {
        if (!in_array($level, self::levels) && $level != '*') {
            throw new InvalidArgumentException('Invalid log level!');
        }
        $this->level = $level === '*' ? self::levels : (array)$level;

        return $this;
    }

    public function age(Carbon $age): static
    {
        if ($age > now()) {
            throw new InvalidArgumentException('Age can not be in the future');
        }
        $this->age = $age;

        return $this;
    }

    public function when($value = null, callable $callback = null, callable $default = null)
    {
        $value = $value instanceof Closure ? $value($this) : $value;

        if (func_num_args() === 0) {
            return new HigherOrderWhenProxy($this);
        }

        if (func_num_args() === 1) {
            return (new HigherOrderWhenProxy($this))->condition($value);
        }

        if ($value) {
            return $callback($this, $value) ?? $this;
        } elseif ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    public function run()
    {
        return LogMessage::query()
            ->withTrashed()
            ->when($this->level, fn($query) => $query->whereIn('level_name', $this->level))
            ->when($this->age, fn($query) => $query->where('created_at', '>=', $this->age))
            ->forceDelete();
    }
}