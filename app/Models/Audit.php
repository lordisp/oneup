<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Audit extends Model
{
    use HasFactory, Uuid;

    protected $fillable = [
        'actor',
        'activity',
        'status',
    ];

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function statusBackground(): Attribute
    {
        return Attribute::make(
            get: fn($value) => [
                'Error' => 'bg-red-100 dark:bg-red-800',
                'Success' => 'bg-green-100 dark:bg-green-800',
            ][$this->status] ?? '',
        );
    }

    public function statusBorder(): Attribute
    {
        return Attribute::make(
            get: fn($value) => [
                'Error' => 'border-red-500',
                'Success' => 'border-green-500',
            ][$this->status] ?? '',
        );
    }

    public function statusText(): Attribute
    {
        return Attribute::make(
            get: fn($value) => [
                'Error' => 'text-red-800 dark:text-red-100',
                'Success' => 'text-green-800 dark:text-green-100',
            ][$this->status] ?? '',
        );
    }
}
