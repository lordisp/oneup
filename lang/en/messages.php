<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'client_created' => 'Client created!',
    'client_deleted' => 'Client deleted!',
    'client_saved' => 'Client saved!',
    'deleted' => 'Deleted!',

    'delete_error' => ':attribute could not be deleted. Try again a little later.',
    'selected_providers' => 'You are currently selecting :attribute provider.',
    'selected_operations' => 'You are currently selecting :attribute operation.',
    'selected' => 'You are currently selecting :attribute :type.',
    'select_all' => 'Do you want to select all :attribute?',

    'access_denied' => 'Access denied!',
    'not_allowed' => 'You don\'t have appropriated permissions.',
    'action_completed' => 'Action completed',
    'all_requests_deleted' => 'All Firewall-Rules have been deleted!',
    'start_delete_all_requests' => 'Start deleting all records...',
    'request_description' => [
        'decommission_request' => 'The connection will be dismantled via the review for firewall-rules through OneUp.',
    ],
    'failed' => [
        'firewall_request_validation' => 'Firewall-Request Validation failed',
        'invalid_operation_argument' => 'Either start or deallocate are allowed arguments',
    ],
    'rule_previously_decommissioned' => 'Rule previously decommissioned!',
    'rule_decommissioned' => 'Decommissioned Firewall-Rule :requestNummer',
    'dispatched_firewall_review_mails' => ':email dispatched Firewall-Review Mails',
];
