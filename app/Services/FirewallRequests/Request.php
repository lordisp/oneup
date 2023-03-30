<?php

namespace App\Services\FirewallRequests;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Request
{
    public function __construct(private array $request)
    {
    }

    public static function normalize($request): array
    {
        return (new self($request))->handle()->request;
    }

    private function handle()
    {
        return $this
            ->moveTags()
            ->setCreatedData()
            ->setRequestorName()
            ->titlingOpenBy()
            ->renameAttributes()
            ->trimSubject();
    }

    private function renameAttributes(): static
    {
        $attributes = [
            'RequestorMail' => 'requestor_mail',
            'RITMNumber' => 'ritm_number',
            'Subject' => 'subject',
            'request_description' => 'description',
        ];

        foreach ($attributes as $old => $new) {
            $value = $this->request[$old];
            $this->request[$new] = $value;
            unset($this->request[$old]);
        }

        unset($this->request['RequestorUID']);

        return $this;
    }

    private function moveTags(): static
    {
        data_set(
            $this->request,
            'request_description', data_get($this->request, 'tag.request_description')
        );
        data_set(
            $this->request,
            'cost_center', data_get($this->request, 'tag.cost_center')
        );

        unset($this->request['tag']);

        return $this;
    }

    private function setCreatedData(): static
    {
        data_set(
            $this->request,
            'created_at',
            Carbon::parse(data_get($this->request, 'created_on'))
        );

        return $this;
    }

    private function setRequestorName(): static
    {
        $requestorName = sprintf("%s %s",
            Str::title(data_get($this->request, 'RequestorFirstName')),
            Str::title(data_get($this->request, 'RequestorLastName'))
        );

        data_set(
            $this->request,
            'requestor_name', $requestorName
        );

        unset(
            $this->request['RequestorFirstName'],
            $this->request['RequestorLastName'],
        );

        return $this;
    }

    private function titlingOpenBy(): static
    {
        data_set($this->request, 'opened_by',
            Str::title(data_get($this->request, 'opened_by'))
        );

        return $this;
    }

    private function trimSubject(): static
    {
        $subject = Str::replace('request', '', Str::lower(data_get($this->request, 'subject')));
        $subject = Str::replace('_', '', $subject);

        data_set(
            $this->request,
            'subject',
            Str::upper($subject)
        );

        return $this;
    }
}