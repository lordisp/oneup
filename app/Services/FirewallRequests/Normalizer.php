<?php

namespace App\Services\FirewallRequests;

use App\Models\BusinessService;
use App\Models\FirewallRule;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


class Normalizer extends Rule
{

    public static function normalize($rule)
    {
        return (new self($rule))->handle();
    }

    private function handle()
    {
        return $this
            ->setConnections()
            ->setPorts()
            ->setEndDate()
            ->setPCI()
            ->generateHash()
            ->convertArraysToJson()
            ->setStatus()
            ->postValidation();
    }

    private function setConnections(): static
    {
        $hasDestinationUrl = array_key_exists('destination_url', $this->rule);
        $this->rule['destination'] = $hasDestinationUrl
            ? $this->trimConnection($this->rule['destination_url'])
            : $this->trimConnection($this->rule['destination']);

        if ($hasDestinationUrl) unset($this->rule['destination_url']);

        $hasSourceUrl = array_key_exists('source_url', $this->rule);
        $this->rule['source'] = $hasSourceUrl
            ? $this->trimConnection($this->rule['source_url'])
            : $this->trimConnection($this->rule['source']);

        if ($hasSourceUrl) unset($this->rule['source_url']);

        $this->rule['source_ips'] = $this->setConnectionIps($this->rule['source']);
        $this->rule['destination_ips'] = $this->setConnectionIps($this->rule['destination']);

        return $this;
    }

    private function setConnectionIps(array $connection): array
    {
        $ips = [];
        foreach ($connection as $value) {
            $value = strstr($value, "/", true) ?: $value;

            if (filter_var($value, FILTER_VALIDATE_IP)) {
                $ips[] = $value;
            }
        }
        return $ips;
    }

    private function setPorts(): static
    {
        $this->rule['destination_port'] = $this->trimPorts($this->rule['destination_port']);

        return $this;
    }

    private function trimConnection(string $string): array
    {
        $string = Str::replace(["\n", ",", " and ", " "], ';', $string);
        $string = Str::replace([";;"], ';', $string);
        $string = Str::replace(["https://", "http://"], '', $string);
        $array = explode(';', $string);
        $array = array_map('trim', $array);
        $array = Arr::flatten($array);
        $array = array_filter($array);
        return array_unique($array);
    }

    private function trimPorts(string $string): array
    {
        $string = Str::replace(["\n", ",", " and ", " - "], ';', $string);
        $string = Str::replace([";;"], ';', $string);
        $string = Str::replace(' ', ';', $string);
        $array = explode(';', $string);
        $array = array_map('trim', $array);
        $array = Arr::flatten($array);
        $array = array_filter($array);
        return array_unique($array);
    }

    private function setEndDate(): static
    {
        try {
            $this->rule['end_date'] = Carbon::parse($this->rule['end_date'])->toDateTimeString();
        } catch (Exception) {
            $this->rule['end_date'] = Carbon::parse(self::FOREVER)->toDateTimeString();
        }
        return $this;
    }

    private function setPCI(): static
    {
        $ips = array_merge(
            data_get($this->rule, 'source_ips'),
            data_get($this->rule, 'destination_ips')
        );

        $pciDss = PCIValidator::bySubnet($ips);

        data_set($this->rule, 'pci_dss', $pciDss);

        unset(
            $this->rule['source_ips'],
            $this->rule['destination_ips'],
        );

        return $this;
    }

    private function postValidation(): static
    {
        if (
            $this->rule['action'] === 'undefined'
            || $this->rule['destination'] === 'undefined'
            || $this->rule['source'] === 'undefined'
            || $this->rule['destination_port'] === 'undefined'
            || $this->rule['service'] === 'undefined'
        ) {
            $this->rule = [];
        }

        return $this;

    }

    private function generateHash(): static
    {
        $source = data_get($this->rule, 'source');
        $destination = data_get($this->rule, 'destination');
        $service = data_get($this->rule, 'service');
        $destination_port = data_get($this->rule, 'destination_port');

        $hash = md5(json_encode($source) . json_encode($destination) . $service . json_encode($destination_port));

        data_set($this->rule, 'hash', $hash);
        return $this;
    }

    private function convertArraysToJson(): static
    {
        $source = json_encode(data_get($this->rule, 'source'));
        $destination = json_encode(data_get($this->rule, 'destination'));
        $destination_port = json_encode(data_get($this->rule, 'destination_port'));

        data_set($this->rule, 'source', $source);
        data_set($this->rule, 'destination', $destination);
        data_set($this->rule, 'destination_port', $destination_port);
        return $this;
    }

    private function setStatus(): static
    {
        $action = data_get($this->rule, 'action');

        if ($action === 'deleted') {
            data_set($this->rule, 'status', 'deleted');
            return $this;
        }

        data_set($this->rule, 'status', 'open');
        return $this;
    }

    public function withBusinessService($businessService): static
    {
        if (empty($this->rule)) {
            return $this;
        }
        if ($businessService instanceof BusinessService) {
            $businessService = $businessService->id;
        }

        data_set($this->rule, 'business_service_id', $businessService);

        return $this;
    }

    public function get(): array|FirewallRule
    {
        return $this->rule;
    }

}