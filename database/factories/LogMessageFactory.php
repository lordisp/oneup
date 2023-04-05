<?php

namespace Database\Factories;

use App\Models\LogMessage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LogMessageFactory extends Factory
{
    const levels = [
        2 => 'API',
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    ];
    protected $model = LogMessage::class;

    public function definition(): array
    {

        $level = array_rand(self::levels);
        $levelName = self::levels[$level];

        return [
            'level_name' => $levelName,
            'level' => $level,
            'message' => $this->faker->word(),
            'logged_at' => Carbon::now(),
            'context' => $this->faker->words(),
            'extra' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function deleted(): LogMessageFactory
    {
        return $this->state([
            'deleted_at' => Carbon::now()->subDays(rand(1, 30))->subYears(rand(3, 10))
        ]);
    }

    public function level(int $level): LogMessageFactory
    {
        return $this->state([
            'level_name' => self::levels[Str::upper($level)],
            'level' => Str::upper($level),
        ]);
    }
}
