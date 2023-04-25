<?php

namespace App\Traits;

use App\Services\TokenCache;

trait Token
{
    protected function token($provider): string
    {
        return (new TokenCache())
            ->provider($provider)
            ->get();
    }

    protected function newToken($provider): string
    {
        return (new TokenCache())
            ->provider($provider)
            ->noCache()
            ->get();
    }
}