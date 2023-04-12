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


    public static function process(string $ruleId, User $user): ClientResponse|HttpResponse
    {
        return (new static())
            ->getRule($ruleId)
            ->normalizeRequest($user)
            ->callServiceNowApi()
            ->notifyUser($user)
            ->clearCache()
            ->container['response'];
    }

    private function getRule(string $ruleId): static
    {
        $rule = FirewallRule::query()
            ->where('id', $ruleId)
            ->forFirewallRequest();

        if ($rule->count() === 0) {
            $this->container['response'] = response("Rule with the Id '{$ruleId}' was not found!", 400);
            return $this;
        }

        cache()->put($this->container['id'], $rule->toArray());

        return $this;
    }

    private function normalizeRequest(User $user): static
    {
        $rule = cache($this->container['id']);

        if ($rule === null) return $this;

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

        unset($rule['business_service_id'],$rule['business_service']);
        $this->container['request']['rules'][] = $rule;

        $this->validateRules($this->container['request']);

        return $this;
    }

    protected function callServiceNowApi(): static
    {
        if (cache($this->container['id']) === null) return $this;

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

        if ($this->container['response']->failed()) {
            Log::error('Failed to Create Firewall Request', (array)$this->container['response']->json());
        }

        if ($this->container['response']->successful()) {
            Log::debug('Create Firewall Request Created', (array)$this->container['response']->json());
        }

        return $this;
    }

    private function notifyUser(User $user): static
    {
        $user->notify(new CreateFirewallRequestNotification($this->container['response']));
        return $this;
    }

    private function clearCache(): static
    {
        cache()->forget($this->container['id']);
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
            $this->clearCache();
        }
        Log::debug('Rules are valid');
    }

    private function normalizeConnections(string $connection): string
    {
        $connection = json_decode($connection, true);

        return implode(', ', $connection);
    }
}