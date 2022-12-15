<?php

namespace App\Jobs\Scim;

use App\Services\Scim;
use App\Traits\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddUserFromAADJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token;

    const PROVIDER = 'lhg_graph';
    protected string $provider;

    public function __construct(protected string $email, string $provider = null)
    {
        $this->provider = $provider ?? self::PROVIDER;
    }

    public function handle()
    {
        $scim = new Scim();
        $scim->provider($this->provider)
            ->users($this->email)
            ->add();
    }


}