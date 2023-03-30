<?php

namespace App\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Log;

trait ValidationRules
{
    protected function firewallValidation($data): \Illuminate\Contracts\Validation\Validator|\Illuminate\Validation\Validator|ValidationException
    {
        return is_array($data) ? Validator::make($data, [
            'rules' => 'required|array',
            'rules.*.action' => 'required|string',
            'rules.*.destination' => 'required|string',
            'rules.*.source' => 'required|string',
            'rules.*.service' => 'required|string',
            'rules.*.destination_port' => 'required|string',
            'rules.*.description' => 'required|string',
            'RequestorMail' => 'email:rfc,dns',
            'RequestorFirstName' => 'required',
            'RequestorLastName' => 'required',
            'RequestorUID' => 'required',
            'RITMNumber' => 'required',
            'opened_by' => 'required',
            'Subject' => 'required',
            'tag' => 'required|array',
            'tag.request_description' => 'required|string',
            'tag.business_service' => 'required|string',

        ]) : ValidationException::withMessages((array)'Invalid File content');
    }


    protected function preValidateFirewallRequestFiles($data)
    {
        if (isset($data) && is_array($data)) {

            $validate = is_array(Arr::first($data)) ? $data : [$data];

            foreach ($validate as $item) {
                try {
                    $validator = Validator::make($item, [
                        'Template' => 'required|string',
                        'request_description' => 'required|string',
                        'rules' => 'required|array',
                        'rules.*.action' => 'required|string|min:1',
                        'rules.*.destination' => 'required|string|min:1',
                        'rules.*.source' => 'required|string|min:1',
                        'rules.*.service' => 'required|string',
                        'rules.*.destination_port' => 'required|string',
                        //'rules.*.description' => 'required|string',
                        'RequestorMail' => 'email',
                        'RequestorFirstName' => 'required',
                        'RequestorLastName' => 'required',
                        'RequestorUID' => 'required',
                        'RITMNumber' => 'required',
                        'opened_by' => 'required',
                        'Subject' => 'required',
                        //'tag' => 'array:business_service',
                        'tag.business_service' => 'required|string|min:4',
                    ]);
                } catch (ValidationException $exception) {
                    Log::error($exception->getMessage());
                    $this->event($exception->getMessage(), 'error');
                    throw $exception;
                }
            }
            return isset($validator) && $validator->fails() ? $validator->errors()->toArray() : [];
        }
    }
}