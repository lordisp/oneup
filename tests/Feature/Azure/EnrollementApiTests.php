<?php

namespace Tests\Feature\Azure;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnrollementApiTests extends TestCase
{
    const billingAccountName = '82133742';
    const enrollmentAccountName = '287118';

    /**
     * Get Role-Assigment, associated to an Enrollment-Account
     * This is to figure out what service principals have been associated to a given Enrollment-Account
     * An Enrollment-Account is a personalized user account. The same account must be a local account in the target tenant. Guest account won't work.
     * @test
     */
    public function billing_role_assignments_get_by_enrollment_account()
    {
        $billingAccountName = self::billingAccountName;
        $enrollmentAccountName = self::enrollmentAccountName;
        $url = "https://management.azure.com/providers/Microsoft.Billing/billingAccounts/{$billingAccountName}/enrollmentAccounts/{$enrollmentAccountName}/billingRoleAssignments?api-version=2019-10-01-preview";

        $response = Http::withToken(env('TOKEN'))->get($url)->json();
        dd($response);
    }

    /**
     * Authorize a service-principal to programmatically provision subscription on behalf of an enrollment-account
     * The Enrollment account must be a local user account in the target tenant. Guest account won't work.
     * @test
     */
    public function authorize_service_principal_for_managing_subscriptions()
    {
        $token = env('TOKEN');
        $billingAccountName = self::billingAccountName; // Deutsche Lufthansa AG (82133742)
        $enrollmentAccountName = self::enrollmentAccountName; // adm_rafael.camison@lhtests.onmicrosoft.com
        $billingRoleAssignmentName = 'a0bcee42-bf30-4d1b-926a-48d21664ef71'; // SubscriptionCreator

        $principalId = '0af7a0f5-c426-447e-bedb-e74c99d22465'; // OneUp_enrollmentManagement-SP1
        $principalTenantId = '83549d3e-32b8-4756-95aa-aef50f1b5076'; // LH Tests
        $roleDefinitionId = "/providers/Microsoft.Billing/billingAccounts/{$billingAccountName}/enrollmentAccounts/{$enrollmentAccountName}/billingRoleDefinitions/a0bcee42-bf30-4d1b-926a-48d21664ef71";

        $url = "https://management.azure.com/providers/Microsoft.Billing/billingAccounts/{$billingAccountName}/enrollmentAccounts/{$enrollmentAccountName}/billingRoleAssignments/{$billingRoleAssignmentName}?api-version=2019-10-01-preview";
        $response = Http::withToken($token)->put($url, [
            'properties' => [
                'principalId' => $principalId,
                'principalTenantId' => $principalTenantId,
                'roleDefinitionId' => $roleDefinitionId,
            ],
        ]);
        dd($response->json());
        $this->assertEquals(200, $response->status());
    }


    /** @test */
    public function list_subscription_by_service_principal()
    {
        $url = "https://management.azure.com/providers/Microsoft.Subscription/operations?api-version=2021-10-01";
        $response = Http::withToken($this->accessToken())->get($url);
        dd(
            $response->status(),
            $response->json(),
        );
    }

    /**
     * @test
     * Create Azure Subscription
     * @var string $workload Allowed values for Workload are Production and DevTest.
     */
    public function can_create_subscription_by_service_principal()
    {
        $displayName = 'LHG_SM_CAMISON_N';
        $workload = 'DevTest';

        $billingAccount = self::billingAccountName;
        $enrollmentAccount = self::enrollmentAccountName;
        $alias = Str::slug($displayName);
        $url = "https://management.azure.com/providers/Microsoft.Subscription/aliases/{$alias}?api-version=2021-10-01";
        $body = [
            'properties' => [
                'billingScope' => "/providers/Microsoft.Billing/BillingAccounts/{$billingAccount}/enrollmentAccounts/{$enrollmentAccount}",
                'DisplayName' => $displayName,
                'Workload' => $workload,
            ]
        ];

        $response = Http::withToken($this->accessToken())->put($url, $body);

        $this->assertEquals(201, $response->status());

        dd(
            $response->json(),
        );
    }

    protected function accessToken()
    {
        $tenant = env('AZURE_TEST_TENANT');
        $url = "https://login.microsoftonline.com/{$tenant}/oauth2/token";
        $body = [
            'grant_type' => 'client_credentials',
            'client_id' => env('AZURE_TEST_CLIENT_ID'),
            'client_secret' => env('AZURE_TEST_CLIENT_SECRET'),
            'resource' => 'https://management.azure.com',
        ];
        return Http::asForm()->post($url, $body)->json('access_token');
    }


}
