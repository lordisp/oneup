<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Log;

class WebhookMiddleware
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('webhook');
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return RedirectResponse|Response|mixed|void
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->filled('data') && !empty($this->config) && $this->validateBody($request->data)) {
            return $next($request);
        } else {
            Log::error('Invalid webhook body');
            return response(status: 400);
        }
    }

    protected function validateBody($body): bool
    {
        $inArray = Arr::has((array)$body, [
            'essentials.alertId',
            'essentials.alertRule',
        ]);
        $inConfig = $inArray && $this->inConfig($body);
        $isString = is_string(Arr::first(data_get((array)$body, 'alertContext.condition.allOf.*.linkToSearchResultsAPI')));
        return $inArray && $isString && $inConfig;
    }

    protected function inConfig($body): bool
    {
        return array_key_exists(data_get($body, 'essentials.alertRule'), $this->config);
    }
}
