<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Services\TokenCache get()
 * @method static \App\Services\TokenCache withoutEncryption()
 * @method static \App\Services\TokenCache jwt(mixed $decryptedToken)
 * @method static \App\Services\TokenCache provider(string $string)
 * @see \App\Services\TokenCache
 */
class TokenCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tokencache';
    }
}