<?php

namespace App\Services\Filter;

use Illuminate\Support\Arr;

class IPAddressFilter
{
    public static function process(array $ip_array): array
    {
        $valid_ips = array_filter($ip_array, function ($ip_address) {

            $parts = explode('/', $ip_address);

            if (
                count($parts) === 2 &&
                ! empty(filter_var(Arr::first($parts), FILTER_VALIDATE_IP))
            ) {
                return true;
            }

            if (filter_var($ip_address, FILTER_VALIDATE_IP)) {
                return true;
            }

            return false;
        });

        $valid_ips = array_map(function ($ip_address) {
            return strpos($ip_address, '/') === false
                ? $ip_address
                : explode('/', $ip_address)[0];
        }, $valid_ips);

        return array_values(array_unique($valid_ips));
    }
}
