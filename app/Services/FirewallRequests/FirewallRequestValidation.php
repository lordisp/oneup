<?php

namespace App\Services\FirewallRequests;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FirewallRequestValidation
{
    protected array $rules;

    protected array $messages;

    public static function validate(array $request)
    {
        return (new static())
            ->setRules()
            ->setMessages()
            ->handle($request);
    }

    protected function handle($request)
    {
        $validator = Validator::make($request, $this->rules, $this->messages);

        if ($validator->fails()) {

            $attributes = Arr::flatten($validator->errors()->getMessages());

            Log::debug(sprintf(
                '%s/%s has %s invalid attributes: %s',
                data_get($request, 'Subject'),
                data_get($request, 'RITMNumber'),
                $validator->errors()->count(),
                implode(', ', $attributes)
            ));

            return [];
        }

        return $validator->getData();
    }

    protected function setRules(): FirewallRequestValidation
    {
        $this->rules = [
            'rules' => 'required|array',
            'tag' => 'required|array',
            'rules.*.action' => 'required|string|min:1',
            'rules.*.destination' => [
                'required_without:rules.*.destination_url',
            ],
            'rules.*.destination_url' => [
                'required_without:rules.*.destination',
            ],
            'rules.*.type_destination' => 'required|string',
            'rules.*.type_source' => 'required|string',
            'rules.*.source' => 'required',
            'rules.*.service' => 'required|string',
            'rules.*.destination_port' => 'required|string',
            'rules.*.description' => 'required|string',
            'rules.*.end_date' => 'nullable',
            'rules.*.pci_dss' => 'nullable',
            'RequestorMail' => 'email',
            'RequestorFirstName' => 'required',
            'RequestorLastName' => 'required',
            'RITMNumber' => 'required|string|starts_with:RITM',
            'opened_by' => 'required',
            'created_on' => 'required',
            'Subject' => 'required|string|starts_with:Request_',
            'tag.request_description' => 'required|string',
            'tag.business_service' => 'required|string',
        ];

        return $this;
    }

    protected function setMessages(): FirewallRequestValidation
    {
        $this->messages = [[
            'request_description' => 'request_description',
            'tag' => 'tag',
            'rules' => 'rules',
            'rules.*.action' => 'rule action',
            'rules.*.destination' => 'rule destination',
            'rules.*.destination_url' => 'rule destination_url',
            'rules.*.type_destination' => 'rule type_destination',
            'rules.*.type_source' => 'rule type_source',
            'rules.*.source' => 'rule source',
            'rules.*.service' => 'rule service',
            'rules.*.destination_port' => 'rules destination_port',
            'rules.*.description' => 'rules description',
            'rules.*.end_date' => 'rules end_date',
            'rules.*.pci_dss' => 'rules pci_dss',
            'RequestorMail' => 'RequestorMail',
            'RequestorFirstName' => 'RequestorFirstName',
            'RequestorLastName' => 'RequestorLastName',
            'RITMNumber' => 'RITMNumber',
            'opened_by' => 'opened_by',
            'created_on' => 'created_on',
            'Subject' => 'Subject',
            'tag.business_service' => 'tag business_service',
            'tag.request_description' => 'tag request_description',
        ]];

        return $this;
    }
}
