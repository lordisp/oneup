<?php

namespace App\Jobs\ServiceNow;

use App\Models\BusinessService;
use App\Models\FirewallRule;
use App\Models\Group;
use App\Models\ServiceNowRequest;
use App\Models\User;
use App\Traits\ValidationRules;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ImportServiceNowFirewallRequestsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidationRules;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public User $User, public $value)
    {
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $invalid = $this->preValidateFirewallRequestFiles($this->value);

        $model = ServiceNowRequest::firstOrNew(['ritm_number' => $this->value['RITMNumber']]);

        if (!$model->exists && empty($invalid)) {

            $businessService = array_key_exists('business_service', $this->value['tag'])
                ? data_get($this->value, 'tag.business_service')
                : null;

            $error = $this->importRequest($model);

            if (isset($this->value['rules'])) foreach ($this->value['rules'] as $rule) {

                $rule['pci_dss'] = $this->isPci(BusinessService::class, 'name', $businessService);
                $rule['business_service'] = $businessService;
                $error = $this->importRule($model, $rule);

            }


            if (!$error) Log::info($this->value['Subject'] . ' imported');
            unset($exception, $message);

        } elseif ($model->exists) {

            $model->rules->filter(function ($rule): void {
                if ($rule->status === 'extended') {
                    $rule->status = $rule->pci_dss ? 'review' : $rule->status;
                    $rule->end_date = $this->setRuleEndDate($rule);
                    $rule->save();
                    Log::info($this->value['Subject'] . ' already exist, reset extended status only for review.');
                };
            });

        } elseif (!empty($invalid)) {
            Log::warning($this->value['Subject'] . ' has ' . count($invalid) . ' invalid attributes');
        }
    }

    protected function importRequest($model): bool
    {
        $model->ritm_number = $this->value['RITMNumber'];
        $model->template = $this->value['Template'];
        $model->description = Str::limit($this->value['request_description'], 255);
        $model->requestor_mail = $this->value['RequestorMail'];
        $model->requestor_name = "{$this->value['RequestorFirstName']} {$this->value['RequestorLastName']}";
        $model->opened_by = $this->value['opened_by'];
        $model->subject = $this->value['Subject'];

        try {
            return !$model->save();
        } catch (QueryException $exception) {
            Log::error('ImportError: ' . $exception->getMessage(), (array)$exception);
            return true;
        }
    }

    protected function importRule($model, $rule): bool
    {
        $rule['end_date'] = $this->setRuleEndDate($rule);
        $rule['status'] = $this->setRuleStatus($rule);
        try {
            $ruleModel = $model->rules()->firstOrNew(
                [
                    'action' => $rule['action'],
                    'destination' => $rule['destination'],
                    'source' => $rule['source'],
                ], $rule
            );
            $ruleModel->end_date = $rule['end_date'];
            $ruleModel->status = $rule['status'];
            $ruleModel->business_service = $rule['business_service'];
            $message = $ruleModel->exists ? 'Update' : 'Create';
            $ruleModel->save();

            Log::info($message . ' rule from ' . $this->value['Subject'], $rule);

        } catch (QueryException $exception) {
            Log::error('ImportServiceNowFirewallRequestsJob: ' . $exception->getMessage(), (array)$exception);
            return true;
        }
        return false;
    }

    protected function syncTags($tags, $ruleModel)
    {

    }

    /**
     * Determines if a Firewall-Rule is under PCI-DSS regulation based on the Business-Service lookup-table
     * @param array|string $model
     * @param string $column
     * @param string $term
     * @return bool
     */
    protected function isPci(array|string $model, string $column, string $term): bool
    {
        return class_exists($model) && !empty($model::where($column, $term)->first());
    }

    /**
     * @param $rule
     * @return Carbon
     */
    protected function setRuleEndDate($rule): Carbon
    {
        if ($rule instanceof FirewallRule) $rule = $rule->toArray();
        $pciReviewDate = now()->addMonths(7);
        try {
            $rule['end_date'] = Carbon::parse($rule['end_date']);
        } catch (InvalidFormatException $e) {
            $rule['end_date'] = $rule['pci_dss'] ? $pciReviewDate : now()->addYear();
            Log::debug('Overwrite "End-date" due to an invalid source value:' . $e->getMessage());
        }

        return $rule['pci_dss'] ? min($pciReviewDate, $rule['end_date']) : min(now()->addYear(), $rule['end_date']);
    }

    /**
     * Reset status based on PCI
     * @param $rule
     * @return string
     */
    protected function setRuleStatus($rule): string
    {
        return $rule['pci_dss'] ? 'review' : 'open';
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
    }
}
