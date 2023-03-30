<?php

namespace App\Services\AzureAD;


use Illuminate\Support\Str;
use InvalidArgumentException;

class UserId
{
    protected string $userId;

    public function __construct(string $userId)
    {
        if (!Str::isUuid($userId)) {
            throw new InvalidArgumentException('It must be a valid uuid (universally unique identifier)!');
        }
        $this->userId = $userId;
    }

    public function get(): string
    {
        return $this->userId;
    }
}