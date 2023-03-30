<?php

namespace App\Jobs\ServiceNow;

use App\Jobs\Scim\ImportUserWithBusinessServiceJob;
use App\Services\Scim;
use App\Services\ServiceNow\GroupMembers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ImportBusinessServiceMemberJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected string $businessService;

    protected string $identifier;

    protected Scim $scim;

    public function __construct(string $businessService, string $identifier)
    {
        $this->businessService = $businessService;

        $this->identifier = $identifier;
    }


    public function retryUntil(): Carbon
    {
        return now()->addMinutes(20);
    }

    public function uniqueId(): string
    {
        return $this->identifier;
    }

    public function handle()
    {
        $emails = $this->getBusinessServiceResponsibles();

        if (empty($emails)) {
            return;
        }

        $this->importUserWithBusinessService($emails);
    }

    protected function getBusinessServiceResponsibles(): array
    {
        return (new GroupMembers($this->businessService, 'Responsibles'))->handle();
    }

    protected function importUserWithBusinessService(array $emails)
    {
        foreach ($emails as $email) {
            ImportUserWithBusinessServiceJob::dispatch($email, $this->businessService)
                ->afterCommit();
        }
    }
}