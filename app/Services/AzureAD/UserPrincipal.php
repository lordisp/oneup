<?php

namespace App\Services\AzureAD;


use Illuminate\Support\Str;
use InvalidArgumentException;

class UserPrincipal
{
    protected string $userPrincipal;

    public function __construct(string $userPrincipal)
    {
        if (!filter_var($userPrincipal, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('It must be a valid email address!');
        }
        $this->userPrincipal = Str::lower($userPrincipal);
    }

    public function get(): string
    {
        return $this->userPrincipal;
    }
}