<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'level_name',
        'level',
        'message',
        'logged_at',
        'context',
        'extra',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'context' => 'array',
        'extra' => 'array',
    ];
}
