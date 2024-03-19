<?php

namespace App\Services\AzureAD;

use InvalidArgumentException;

/**
 * It validates the provided properties for use of the azure graph api to get specific user properties
 *
 * @link  https://learn.microsoft.com/en-us/graph/api/resources/riskyuser?view=graph-rest-1.0#properties
 */
class RiskyUserProperties
{
    protected string $properties;

    protected array $validProperties = [
        '@odata.type',
        'id',
        'isDeleted',
        'isProcessing',
        'riskLastUpdatedDateTime',
        'riskLevel',
        'riskState',
        'riskDetail',
        'userDisplayName',
        'userPrincipalName',
    ];

    public function __construct(string|array $properties)
    {
        if (is_string($properties)) {
            $properties = explode(',', $properties);
            $properties = array_map('trim', $properties);
        }

        foreach ($properties as $property) {
            if (! in_array($property, $this->validProperties)) {
                throw new InvalidArgumentException(sprintf('"%s" property is not allowed. For more information, please visit: https://learn.microsoft.com/en-us/graph/api/resources/riskyuser?view=graph-rest-1.0#properties', $property));
            }
        }

        $this->properties = implode(',', $properties);
    }

    public function get(): string
    {
        return $this->properties;
    }
}
