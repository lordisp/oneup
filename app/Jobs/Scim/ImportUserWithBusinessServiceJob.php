<?php

namespace App\Jobs\Scim;

use App\Services\Scim;
use DateTime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportUserWithBusinessServiceJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PROVIDER = 'lhg_graph';


    protected string $uniqueId;

    public function __construct(protected string $email, protected string $businessService)
    {
        $this->uniqueId = md5(Str::lower($this->email) . Str::lower($this->businessService));
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->uniqueId),
            new ThrottlesExceptions(10, 5)
        ];
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }

    public function handle()
    {
        (new Scim())
            ->provider(self::PROVIDER)
            ->users($this->email)
            ->withBusinessService($this->businessService)
            ->add();
    }
}