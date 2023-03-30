<?php

namespace App\Facades\AzureAD;

use App\Services\AzureAD\UserId;
use App\Services\AzureAD\UserPrincipal;
use App\Services\AzureAD\UserProperties;
use Illuminate\Support\Facades\Facade;

/**
 * @method static select(UserProperties $param)
 * @method static get(UserPrincipal|UserId $principalId)
 */
class User extends Facade
{

    protected static function getFacadeAccessor(): string
    {
        return 'user';
    }
}