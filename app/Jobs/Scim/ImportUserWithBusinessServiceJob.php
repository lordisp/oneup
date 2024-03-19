<?php

namespace App\Jobs\Scim;

use App\Services\Scim;
use DateTime;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportUserWithBusinessServiceJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const PROVIDER = 'lhg_graph';

    protected string $uniqueId;

    public function __construct(protected string $email, protected string $businessService)
    {
        $this->uniqueId = md5(Str::lower($this->email).Str::lower($this->businessService));
    }

    public function retryUntil(): DateTime
    {
        return now()->addMinutes(5);
    }

    public function handle(): void
    {
        (new Scim())
            ->provider(self::PROVIDER)
            ->users($this->email)
            ->withBusinessService($this->businessService)
            ->add();
    }
}
