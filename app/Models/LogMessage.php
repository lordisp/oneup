<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogMessage extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'level_name',
        'level',
        'message',
        'logged_at',
        'context',
        'extra',
        'deleted_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'context' => 'array',
        'extra' => 'array',
    ];

    public function scopeTrashedBy(Builder $query, string|array $levelName, Carbon $age): Builder
    {
        return $query
            ->withTrashed()
            ->whereIn('level_name', (array)$levelName)
            ->where('deleted_at', '<', $age);
    }
}
