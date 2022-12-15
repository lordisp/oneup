<?php

namespace App\Listeners;

use App\Jobs\Scim\AddUserFromAADJob;
use App\Models\ServiceNowRequest;

class FirewallImportAddUsersListener
{
    public function __construct()
    {
    }

    public function handle(): void
    {
        $emails = $this->collectEmails();
        foreach ($emails as $email) {
            AddUserFromAADJob::dispatch($email);
        }
    }

    protected function collectEmails()
    {
        return ServiceNowRequest::whereRelation('rules', 'pci_dss', true)
            ->get()
            ->unique('requestor_mail')
            ->map
            ->requestor_mail
            ->toArray();
    }
}