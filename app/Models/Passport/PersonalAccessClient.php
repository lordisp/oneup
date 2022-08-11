<?php

namespace App\Models\Passport;

use App\Traits\Uuid;
use Laravel\Passport\PersonalAccessClient as PassportAccessClient;

class PersonalAccessClient extends PassportAccessClient
{
    use Uuid;
}
