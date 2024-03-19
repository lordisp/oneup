<?php

namespace App\Services\ServiceNow;

use Illuminate\Support\Facades\Validator;

class FirewallRequestValidation
{
    protected \Illuminate\Validation\Validator $request;

    public function __construct(array $request)
    {
        $this->request = $this->validate($request);
    }

    private function validate(array $request)
    {
        return Validator::make($request, [
            'request_description' => 'required|string',
            'requestor_mail' => 'required|email',
            'opened_by' => 'required|email',
            'business_service' => 'required|string',
            'cost_center' => 'required|string',
            'rules' => 'required|array',
            'rules.*.action' => 'in:add,delete',
            'rules.*.type_destination' => 'in:ip_address_dest,url_destination',
            'rules.*.destination' => 'string|required',
            'rules.*.type_source' => 'in:ip_address_source,url_source',
            'rules.*.source' => 'string|required',
            'rules.*.service' => 'string|required',
            'rules.*.destination_port' => 'string|required',
            'rules.*.description' => 'string|required',
            'rules.*.end_date' => 'required_if::rules.*.no_expiry,No',
            'rules.*.pci_dss' => 'in:Yes,No',
            'rules.*.no_expiry' => 'in:Yes,No',
            'rules.*.nat_required' => 'in:Yes,No',
            'rules.*.application_id' => 'string',
            'rules.*.contact' => 'string',
            'rules.*.business_purpose' => 'string',
        ]);
    }

    public function get()
    {
        return $this->request;
    }
}
