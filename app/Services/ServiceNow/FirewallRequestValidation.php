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
            "request_description" => "required|string",
            "requestor_mail" => "required|email",
            "opened_by" => "required|email",
            "business_service" => "required|string",
            "cost_center" => "required|string",
            "rule" => "array",
            "rule.action" => "in:add,delete",
            "rule.type_destination" => "in:ip_address_dest,url_destination",
            "rule.destination" => "string|required",
            "rule.type_source" => "in:ip_address_source,url_source",
            "rule.source" => "string|required",
            "rule.service" => "string|required",
            "rule.destination_port" => "string|required",
            "rule.description" => "string|required",
            "rule.end_date" => "required_if::rules.*.no_expiry,No",
            "rule.pci_dss" => "in:Yes,No",
            "rule.no_expiry" => "in:Yes,No",
            "rule.nat_required" => "in:Yes,No",
            "rule.application_id" => "string",
            "rule.contact" => "string",
            "rule.business_purpose" => "string"
        ]);

    }

    public function get()
    {
        return $this->request;
    }
}