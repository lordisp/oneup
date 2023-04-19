<?php

namespace App\Services\ServiceNow;

use App\Models\FirewallRule;
use App\Models\User;
use App\Notifications\CreateFirewallRequestNotification;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateFirewallRequest
{
    private array $container;

    public function __construct()
    {
        $this->container = [
            'cost-center' => '068111',
            'id' => Str::random(40),
            'response' => response('Processing...', 102)
        ];
    }


    public static function process(FirewallRule $rule, User $user): ClientResponse|HttpResponse
    {
        return (new static())
            ->getRule($rule)
            ->normalizeRequest($user)
            ->callServiceNowApi($user)
            ->setAudit($rule, $user)
            ->notifyUser($user)
            ->container['response'];
    }

    private function getRule(FirewallRule $rule): static
    {

        if (!$rule->exists) {
            $this->container['response'] = response("Rule was not found!", 400);
            return $this;
        }

        $status = FirewallRule::whereId($rule->id)->first()->status;

        if ($status === 'deleted') {
            $this->container['response'] = response(__('messages.rule_previously_decommissioned'), 400);
            return $this;
        }
        $this->container['rule'] = $rule;

        return $this;
    }

    private function normalizeRequest(User $user): static
    {
        if ($this->container['response']->status() != 102) return $this;

        $rule = $this->container['rule'];
        $rule = $rule->toArray();
        $rule['action'] = 'delete';
        $rule['destination'] = $this->normalizeConnections($rule['destination']);
        $rule['source'] = $this->normalizeConnections($rule['source']);
        $rule['destination_port'] = $this->normalizeConnections($rule['destination_port']);
        $rule['end_date'] = ($rule['no_expiry'] === 'No') ? Carbon::parse($rule['end_date'])->toDateString() : '';
        $rule['pci_dss'] = $rule['pci_dss'] ? "Yes" : "No";

        $this->container['request']['business_service'] = $rule['business_service']['name'];
        $this->container['request']['request_description'] = __('messages.request_description.decommission_request');
        $this->container['request']['requestor_mail'] = $user->email;
        $this->container['request']['opened_by'] = $user->email;
        $this->container['request']['cost_center'] = $this->container['cost-center'];

        unset($rule['audits'], $rule['business_service_id'], $rule['business_service']);
        $this->container['request']['rules'][] = $rule;

        $this->validateRules($this->container['request']);

        return $this;
    }

    protected function callServiceNowApi(User $user): static
    {
        if ($this->container['response']->status() != 102) return $this;

        Log::debug('API Payload', $this->container['request']);

        $this->container['response'] = Http::withBasicAuth(config('servicenow.client_id'), config('servicenow.client_secret'))
            ->retry(15, 50, function ($exception) {
                if ($exception->response->status() === 400) {
                    return false;
                }
                return $exception instanceof RequestException && $exception->response->status() === 408;
            }, false)
            ->post(config('servicenow.uri') . '/api/delag/retrieve_cost_centers/CreateCatalogItem',
                $this->container['request']
            );

        return $this;
    }

    private function setAudit(FirewallRule $rule, User $user): static
    {
        $audits = [];

        if ($this->container['response']->status() >= 500 || $this->container['response']->status() >= 400 && $this->container['response']->status() < 500) {
            $audits = [
                'message' => 'Failed to file service-now request',
                'status' => 'Error',
            ];

            $response = $this->container['response'] instanceof HttpResponse
                ? $this->container['response']->content()
                : $this->container['response']->json();

            Log::error($audits['message'], (array)$response);
        }

        if ($this->container['response']->status() >= 200 && $this->container['response']->status() < 300) {
            $audits = [
                'message' => 'Successfully filed service-now request',
                'status' => 'Success',
            ];
            Log::info(sprintf("%s %s", $audits['message'], $user->email), (array)$this->container['response']->json());
        }

        if ($rule->exists) {
            $rule->audits()->create([
                'actor' => $user->email,
                'activity' => $audits['message'],
                'status' => $audits['status'],
            ]);
        }

        return $this;
    }

    private function notifyUser(User $user): static
    {
        $user->notify(new CreateFirewallRequestNotification($this->container['response']));
        return $this;
    }

    private function validateRules(array $request): void
    {
        $validation = (new FirewallRequestValidation($request))->get();

        if ($validation->fails()) {
            Log::error(__('messages.failed.firewall_request_validation'), [
                'errors' => $validation->errors(),
                'data' => $validation->getData(),
            ]);

            $countErrors = count($validation->errors());
            $this->container['response'] = response("Rule validation failed with {$countErrors} errors!", 400);
        }
        Log::debug('Rules are valid');
    }

    private function normalizeConnections(string $connection): string
    {
        $connection = json_decode($connection, true);

        return implode(', ', $connection);
    }
}