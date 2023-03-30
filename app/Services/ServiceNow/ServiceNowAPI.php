<?php

namespace App\Services\ServiceNow;

abstract class ServiceNowAPI
{
    protected string $clientId;
    protected string $secret;
    protected string $uri;
    protected int $sleepMilliseconds = 50;
    protected int $times = 3;

    public function __construct()
    {
        $this->clientId = config('servicenow.client_id');
        $this->secret = config('servicenow.client_secret');
        $this->uri = config('servicenow.uri');
    }
}