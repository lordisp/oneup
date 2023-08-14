<?php

namespace App\Jobs;

use App\Traits\DeveloperNotification;
use App\Traits\Token;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DismissRiskyUsersJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token, DeveloperNotification;

    const PROVIDER = 'lhg_graph';

    public function __construct(private array $userIds)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $response = Http::withToken(decrypt($this->token(self::PROVIDER)))
            ->retry(5, 50, function ($exception, $request) {
                if ($exception instanceof RequestException && $exception->getCode() >= 402) {

                    $this->sendDeveloperNotification($exception);

                    $this->fail($exception);

                    return false;
                }

                $request->withToken(decrypt($this->newToken(self::PROVIDER)));

                return true;
            }, false)
            ->post('https://graph.microsoft.com/v1.0/identityProtection/riskyUsers/dismiss', [
                'userIds' => $this->userIds
            ]);

        if ($response->successful()) {
            Log::info('Updated User-Risk State', $this->userIds);
        }
    }
}
