<?php

namespace App\Jobs;

use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DismissRiskyUsersJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token, DeveloperNotification;

    const PROVIDER = 'lhg_graph';

    public function __construct(private array $userIds)
    {
    }

    public function uniqueId(): string
    {
        return md5(json_encode($this->userIds));
    }

    public function uniqueVia(): Repository
    {
        return Cache::driver(config('app.env') == 'production' ? 'redis' : 'array');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withToken(decrypt($this->token(self::PROVIDER)))
            ->retry(5, 0, function ($exception, $request) {
                if ($exception instanceof RequestException && $exception->getCode() === 400) {

                    $this->sendDeveloperNotification($exception);

                    $this->fail($exception->getMessage());

                    return false;
                }
                if ($exception instanceof RequestException and $exception->getCode() === 429) {
                    sleep($exception->response->header('Retry-After') ?? 10);
                    return true;
                }
                $request->withToken(decrypt($this->newToken(self::PROVIDER)));

                return true;
            }, false)
            ->post('https://graph.microsoft.com/v1.0/identityProtection/riskyUsers/dismiss', [
                'userIds' => $this->userIds
            ]);

        if ($response->failed()) {
            Log::error('Failed to update User-Risk State', [
                'service' => 'risky-users',
                'status' => $response->status(),
                'reason' => $response->reason(),
            ]);
            $this->fail($response->reason());
            return;
        }
        Log::info('Updated User-Risk State', [
            'service' => 'risky-users',
            'ids' => $this->userIds
        ]);
    }
}
