<?php

return [
    'queues' => [
        'import' => env('FW_QUEUE_IMPORT', 'default'),
        'notify' => env('FW_QUEUE_NOTIFY', 'default'),
        'cleanup' => env('FW_QUEUE_CLEANUP', 'default'),
    ],
];
