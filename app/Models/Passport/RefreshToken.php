<?php

namespace App\Models\Passport;

use App\Traits\Uuid;
use Laravel\Passport\RefreshToken as PassportRefreshToken;

class RefreshToken extends PassportRefreshToken
{
    use Uuid;
}
