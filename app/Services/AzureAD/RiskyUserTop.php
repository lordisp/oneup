<?php

namespace App\Services\AzureAD;

use InvalidArgumentException;

/**
 * It validates the provided properties for use of the azure graph api to get specific user properties
 * @link  https://learn.microsoft.com/en-us/graph/api/resources/riskyuser?view=graph-rest-1.0#properties
 */
class RiskyUserTop
{

    protected string $top;

    public function __construct(int $top)
    {
        if ($top < 1 || $top > 500) {
            throw new InvalidArgumentException('Top must be between 1 and 500');
        }
        $this->top = $top;
    }

    public function get(): int
    {
        return $this->top;
    }
}




