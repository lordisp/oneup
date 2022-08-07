<?php

namespace App\Models\Passport;

use App\Traits\Uuid;
use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    use Uuid;
}
