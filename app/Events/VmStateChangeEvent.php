<?php

namespace App\Events;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Events\Dispatchable;
use InvalidArgumentException;

class VmStateChangeEvent
{
    use Dispatchable, Queueable;

    public function __construct(public string $operation, public string $id, public string $vmName)
    {
        if ($operation != 'deallocate' && $operation != 'start') {
            throw new InvalidArgumentException(__('messages.failed.invalid_operation_argument'));
        }
    }
}
