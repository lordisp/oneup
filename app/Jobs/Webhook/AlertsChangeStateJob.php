<?php

namespace App\Jobs\Webhook;

use App\Traits\Token;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlertsChangeStateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Token;

    public function __construct(
        public string $alertId,
        public string $alertState,
        public string $scope,
        public string $comment = ''
    ) {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $provider = 'lhg_arm';
        $alertId = basename($this->alertId);
        $url = 'https://management.azure.com/'.$this->scope.'/providers/Microsoft.AlertsManagement/alerts/'.$alertId.'/changestate?api-version=2019-03-01&newState='.$this->alertState;
        $response = Http::withToken(decrypt($this->token($provider)))
            ->retry(20, 200, function ($exception, $request) use ($provider) {
                if (! $exception instanceof RequestException || $exception->response->status() !== 401) {
                    return false;
                }
                $request->withToken($this->token($provider));

                return true;
            }, throw: false)
            ->post($url, ['comment' => $this->comment]);
        if ($response->status() === 200) {
            Log::debug('Close alert with the Id: '.$response->json('id'));
        }
        if ($response->failed()) {
            Log::error(data_get($response->json('error'), 'message'));
        }
    }
}
