<?php

namespace App\Traits;

use App\Facades\TokenCache;

trait Token
{
    /**
     * Acquire an access-token for api calls
     * @param $provider
     * @return string
     */
    protected function token($provider): string
    {
        return decrypt(TokenCache::provider($provider)->get());
    }
}