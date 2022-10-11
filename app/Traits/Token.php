<?php

namespace App\Traits;

use App\Services\TokenCache;

trait Token
{
    /**
     * Acquire an access-token for api calls
     * @param $provider
     * @return string
     */
    protected function token($provider): string
    {
        return (new TokenCache())->provider($provider)->get();
    }
}