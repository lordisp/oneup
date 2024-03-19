<?php

namespace App\Services\Pdns;

class Normalize
{
    public static function withSubscriptionsQuery(array $subscriptionIds): string
    {
        $query = implode(' or subscriptionId == ', array_map(fn ($key) => "\"{$key}\"", $subscriptionIds));

        return " | where subscriptionId == {$query}";
    }

    public static function skippedSubscriptionsQuery(array $subscriptionIds): string
    {
        $query = implode(' and subscriptionId != ', array_map(fn ($key) => "\"{$key}\"", $subscriptionIds));

        return " | where subscriptionId != {$query}";
    }
}
