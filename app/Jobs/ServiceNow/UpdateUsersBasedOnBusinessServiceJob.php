<?php

namespace App\Jobs\ServiceNow;

use App\Models\BusinessService;
use App\Models\User;
use App\Services\Scim;
use App\Services\ServiceNow\GroupMembers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUsersBasedOnBusinessServiceJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private BusinessService $businessService)
    {
    }

    protected function uniqueId(): string
    {
        return $this->businessService->id;
    }

    public function handle(): void
    {
        $businessService = $this->getBusinessService();

        $this->getBusinessServiceMembersFromServiceNow($businessService);

        $this->addOrUpdateBusinessServiceMembers($businessService);

        $this->removeBusinessServiceMembers($businessService);
    }


    private function getBusinessServiceMembersFromServiceNow(BusinessService $businessService): void
    {
        $this->container['businessServiceName'] = $businessService->name;

        $this->container['businessServiceResponsibles'] = (new GroupMembers($businessService->name, ['Responsibles']))->handle();
    }

    private function addOrUpdateBusinessServiceMembers(BusinessService $businessService): void
    {
        (new Scim())
            ->provider('lhg_graph')
            ->withBusinessService($businessService->name)
            ->users($this->container['businessServiceResponsibles'])
            ->add();
    }

    private function removeBusinessServiceMembers(BusinessService $businessService): void
    {
        $emailsToRemove = $this->getEmailsToRemove($businessService);
        $userIdsToRemove = $this->getUserIdsByEmails($emailsToRemove);

        $businessService->users()->detach($userIdsToRemove);

        $removedEmails = $businessService->users()->pluck('email')->toArray();

        $this->auditBusinessService($businessService, $emailsToRemove, $removedEmails);
    }

    private function getEmailsToRemove(BusinessService $businessService): array
    {
        $currentEmails = $businessService->users()->pluck('email')->toArray();
        $requiredEmails = $this->container['businessServiceResponsibles'];

        return array_diff($currentEmails, $requiredEmails);
    }

    private function getUserIdsByEmails(array $emails): array
    {
        return User::whereIn('email', $emails)->pluck('id')->toArray();
    }

    private function auditBusinessService(BusinessService $businessService, array $initialEmails, array $removedEmails): void
    {
        $businessService->audits()->create([
            'actor' => 'UpdateUsersBasedOnBusinessServiceJob',
            'activity' => 'Removed Business-Service Members',
            'status' => 'Success',
            'metadata' => [
                'business_service' => $businessService->name,
                'oldValue' => $initialEmails,
                'newValue' => array_diff($initialEmails, $removedEmails),
                'diff' => $removedEmails,
            ]
        ]);
    }

    public function getBusinessService(): BusinessService
    {
        return $this->businessService;
    }
}
