<?php

namespace App\Listeners;

use App\Events\VmStateChangeEvent;
use App\Traits\Token;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VmStateChangeProcessListener
{
    use Token;

    const PROVIDER = 'lhg_arm';

    public function handle(VmStateChangeEvent $event): void
    {
        $this->callApi($event);
    }

    private function callApi($event): void
    {
        $response = Http::withToken(decrypt($this->token(self::PROVIDER)))
            ->retry(5, 0, function ($exception, $request) {
                if ($exception instanceof RequestException and $exception->getCode() === 429) {
                    sleep($exception->response->header('Retry-After') ?? 10);
                    return true;
                }
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->newToken(self::PROVIDER)));
                return true;
            }, throw: false)
            ->post(sprintf("https://management.azure.com%s/{$event->operation}?api-version=2023-07-01", $event->id));

        if ($response->failed()) {
            Log::error(sprintf("Failed to %s %s. %s", $event->operation, $event->vmName, $response->reason()),['VmStartStop']);
            return;
        }

        Log::info(sprintf("%s %s.", Str::title($event->operation), $event->vmName),['VmStartStop']);
    }
}
