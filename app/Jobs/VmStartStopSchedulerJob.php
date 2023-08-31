<?php

namespace App\Jobs;

use App\Traits\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VmStartStopSchedulerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token;

    protected string $timezone = '';

    public function __construct()
    {
        $this->timezone = config('services.scheduler.vm-start-stop-scheduler.timezone');
    }

    /**
     * @throws \Throwable
     */
    public function handle(): void
    {
        if (!config('services.scheduler.vm-start-stop-scheduler.enabled')) {
            return;
        }

        $servers = $this->getServerList();

        foreach ($servers as $server) {
            $server = $this->normalizeServer($server);

            $jobs[] = new VmStartStopProcess($server);
        }

        if (!empty($jobs)) {
            Bus::batch($jobs)
                ->name('vm-start-stop-scheduler')
                ->dispatch();
        }

    }

    private function getServerList(): array
    {
        $token = decrypt($this->token('lhg_graph'));

        $url = "https://graph.microsoft.com/v1.0/sites/lufthansagroup.sharepoint.com/drives/b!-wUp0h0GOEiIJXb9iEfdAikgMp-EVrBJig5eJNEqyUFv1u2jjdV_QKywhUjwFX3F/items/01K2ZHOAECXE3XURD4SRDZUOVPJDHIU4LI/workbook/worksheets/scheduler/usedRange";

        $response = Http::withToken($token)
            ->retry(5, 0, function ($exception, $request) {
                if ($exception instanceof RequestException and $exception->getCode() === 429) {
                    sleep(($exception->response->header('Retry-After') ?? 10));
                    return true;
                }
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return true;
                }
                $request->withToken(decrypt($this->newToken('lhg_graph')));
                return true;
            }, throw: false)
            ->get($url);

        if ($response->failed()) {
            Log::error($response->reason(), ['VmStartStop']);
            $this->release(now()->addSeconds(10));
        }

        $values = $response->json('values') ?? [];

        if (count($values) < 2) {
            return [];
        }

        unset($values[0]);

        return array_values($values);
    }

    private function normalizeServer(array $server): array
    {
        $normalized['vmName'] = data_get($server, '0');
        $normalized['subscription'] = data_get($server, '1');
        $normalized['from'] = $this->createFromFormat(data_get($server, '2'));
        $normalized['to'] = $this->createFromFormat(data_get($server, '3'));
        $normalized['week'] = Str::lower(data_get($server, '4'));
        $normalized['status'] = Str::lower(data_get($server, '5'));
        return $normalized;
    }

    private function createFromFormat(string $date): Carbon
    {
        $now = now()->setTimezone($this->timezone);

        return Carbon::createFromFormat('H:i', $date)->setDate(
            $now->year,
            $now->month,
            $now->day,
        );
    }
}
