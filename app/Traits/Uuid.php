<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Uuid
{
    /** @noinspection PhpMultipleClassDeclarationsInspection */
    public static function boot(): void
    {
        // Boot other traits on the Model
        parent::boot();

        /**
         * Listen for the creating event on the user model.
         * Sets the 'id' to a UUID using Str::uuid() on the instance being created
         */
        static::creating(function ($model) {
            if ($model->getKey() === null) {
                $model->setAttribute($model->getKeyName(), Str::orderedUuid()->toString());
            }
        });
    }

    // Tells the database not to auto-increment this field
    public function getIncrementing(): bool
    {
        return false;
    }

    // Helps the application specify the field type in the database
    public function getKeyType(): string
    {
        return 'string';
    }
}
