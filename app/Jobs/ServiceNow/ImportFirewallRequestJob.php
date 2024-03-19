<?php

namespace App\Jobs\ServiceNow;

use App\Models\BusinessService;
use App\Models\ServiceNowRequest;
use App\Models\User;
use App\Services\FirewallRequests\FirewallRequestValidation;
use App\Services\FirewallRequests\Normalizer;
use App\Services\FirewallRequests\Request;
use App\Services\FirewallRequests\RulePCI;
use App\Services\FirewallRequests\RuleStatus;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportFirewallRequestJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $container;

    public function __construct(public User $User, public array $firewallRequest)
    {
        $this->container['review'] = now()->subQuarter();
    }

    public function handle(): void
    {
        $this
            ->validate()
            ->createBusinessServiceInstance()
            ->normalizeRequest()
            ->normalizeRules()
            ->createRequestInstance()
            ->insert()
            ->update()
            ->importBusinessServiceMember();
    }

    private function validate(): static
    {
        $this->container['data'] = FirewallRequestValidation::validate($this->firewallRequest);

        return $this;
    }

    private function createRequestInstance(): static
    {
        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        $ritmNumber = data_get($this->container, 'data.ritm_number');
        $data = data_get($this->container, 'data');

        unset($data['ritm_number']);

        $request = ServiceNowRequest::firstOrNew(
            ['ritm_number' => $ritmNumber], $data
        );

        data_set($this->container, 'request', $request);
        data_set($this->container, 'exists', $request->exists);

        return $this;
    }

    private function createBusinessServiceInstance(): static
    {
        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        data_set(
            $this->container,
            'business_service',
            BusinessService::firstOrCreate(['name' => data_get($this->container, 'data.tag.business_service')])
        );

        return $this;
    }

    private function normalizeRules(): static
    {
        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        $rules = data_get($this->container, 'data.rules');

        foreach ($rules as $key => $rule) {
            $rules[$key] = Normalizer::normalize($rule)
                ->withBusinessService(data_get($this->container, 'business_service'))
                ->get();

            if (empty($rules[$key])) {
                unset($rules[$key]);
            }
        }

        data_set($this->container, 'rules', $rules);
        unset($this->container['data']['rules']);

        return $this;
    }

    private function normalizeRequest(): static
    {
        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        data_set(
            $this->container,
            'data',
            Request::normalize(data_get($this->container, 'data'))
        );

        return $this;
    }

    private function insert(): static
    {

        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        $request = data_get($this->container, 'request');

        if (data_get($this->container, 'exists')) {
            return $this;
        }

        if (empty(data_get($this->container, 'rules'))) {
            return $this;
        }

        Log::debug(sprintf('Insert Request %s', $request->subject));

        try {
            $request->save();
        } catch (\Exception $exception) {
            Log::debug(sprintf('Insert Request Failed with %s', $exception->getMessage()));

            return $this;
        }

        $rules = $request->rules()->createMany(
            data_get($this->container, 'rules')
        );

        foreach ($rules as $rule) {
            $rule->audits()->create([
                'actor' => $this->User->email,
                'activity' => 'Import rule',
                'status' => 'Success',
            ]);
        }

        return $this;
    }

    private function update(): static
    {
        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        $request = data_get($this->container, 'request');
        $exists = data_get($this->container, 'exists');

        if (! $exists) {
            return $this;
        }
        $rules = $request->rules;

        Log::debug(sprintf('Update %s Rules from Request %s', $rules->count(), $request->subject));
        foreach ($rules as $rule) {
            RuleStatus::reset($rule);
            RulePCI::reset($rule);
        }

        foreach ($rules as $rule) {
            $rule->audits()->create([
                'actor' => $this->User->email,
                'activity' => 'Update rule',
                'status' => 'Success',
            ]);
        }

        return $this;
    }

    private function importBusinessServiceMember(): static
    {
        if (empty(data_get($this->container, 'data'))) {
            return $this;
        }

        $businessService = data_get($this->container, 'business_service');

        if (! empty($businessService)) {
            ImportBusinessServiceMemberJob::dispatch($businessService->name, $businessService->name)
                ->afterCommit();
        }

        return $this;
    }
}
