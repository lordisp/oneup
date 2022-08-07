<?php

namespace App\Models\Passport;

use App\Traits\Uuid;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    use Uuid;
}
