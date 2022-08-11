<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static get()
 * @method static withoutEncryption()
 * @method static jwt(mixed $decryptedToken)
 * @method static provider(string $string)
 */
class TokenCache extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tokencache';
    }
}