<?php

return [
    'subscription_id' => env('HUB_SUBSCRIPTION'),
    'resource_group' => env('HUB_RESOURCE_GROUP'),
    'skip_providers' => env('SKIP_PROVIDERS', ''),
    'pdns_lhg_enabled' => env('PDNS_LHG_ENABLED', true),
    'pdns_aviatar_enabled' => env('PDNS_AVIATAR_ENABLED', true),
    'queue_name' => env('PDNS_QUEUE_NAME', 'pdns'),
];
