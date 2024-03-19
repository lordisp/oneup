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
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ImportBusinessServiceMemberJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $businessService;

    protected string $identifier;

    protected Scim $scim;

    public function __construct(string $businessService, string $identifier)
    {
        $this->businessService = $businessService;

        $this->identifier = md5($identifier);
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
            $emails = $this->getBusinessServiceEscalationNotification();
        }

        if (empty($emails)) {
            $emails = $this->getBusinessServiceSecurityContacts();
        }

        if (empty($emails)) {
            Log::info('No contacts found for business service: '.$this->businessService);

            return;
        }

        $this->importUserWithBusinessService($emails);
    }

    protected function getBusinessServiceResponsibles(): array
    {
        Log::debug('Getting responsibles for business service: '.$this->businessService);

        return (new GroupMembers($this->businessService, 'Responsibles'))->handle();
    }

    protected function getBusinessServiceEscalationNotification(): array
    {
        Log::debug('Getting Escalation for business service: '.$this->businessService);

        return (new GroupMembers($this->businessService, 'EscalationNotification'))->handle();
    }

    protected function getBusinessServiceSecurityContacts(): array
    {
        Log::debug('Getting SecurityContacts for business service: '.$this->businessService);

        return (new GroupMembers($this->businessService, 'SecurityContacts'))->handle();
    }

    protected function importUserWithBusinessService(array $emails): void
    {
        Log::debug('Importing users with business service: '.$this->businessService, $emails);
        foreach ($emails as $email) {
            $jobs[] = new ImportUserWithBusinessServiceJob($email, $this->businessService);
        }

        if (! empty($jobs)) {
            Bus::chain($jobs)
                ->dispatch();
        }

    }
}
