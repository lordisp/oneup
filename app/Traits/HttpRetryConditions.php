<?php

namespace App\Traits;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;

trait HttpRetryConditions
{
    /**
     * @discription The handleRequestExceptionConditions method is used to handle the exceptions thrown by the API.
     *
     * @throws Exception
     */
    protected function handleRequestExceptionConditions(Exception $exception, string $exceptionClass): void
    {
        if (! is_subclass_of($exceptionClass, Exception::class)) {
            throw new \InvalidArgumentException('The exception class must be a subclass of Exception. Either use the Exception class or an extension of it.');
        }

        if ($exception instanceof RequestException and $exception->getCode() === 400) {
            throw new $exceptionClass(json_encode(json_decode($exception->response->body(), true)['error']), 400);
        }
        if ($exception instanceof RequestException and $exception->getCode() === 403) {
            throw new $exceptionClass(json_encode(json_decode($exception->response->body(), true)['error']), 403);
        }
        if ($exception instanceof RequestException and $exception->getCode() >= 500) {
            throw new $exceptionClass(json_encode(json_decode($exception->response->body(), true)['error']), 500);
        }
    }

    /**
     * @throws Exception
     *
     * @discription The handleRetryConditions method is used to handle the retry conditions.
     */
    protected function handleRetryConditions($exception, $request, Exception|string $options): bool
    {
        if (! $exception instanceof RequestException) {
            return true;
        }

        $codeHandlers = [
            401 => 'handleUnauthorizedException',
            404 => 'handleNotFoundException',
            429 => 'handleTooManyRequestsException',
        ];

        $handler = $codeHandlers[$exception->getCode()] ?? 'defaultHandler';

        return $this->$handler($exception, $request, $options);
    }

    private function handleUnauthorizedException($exception, $request, $options): bool
    {
        if (is_subclass_of($options, Exception::class)) {
            $this->throwCustomException($exception, $options);
        }

        $request->withToken(decrypt($this->newToken($options)));

        return true;
    }

    private function handleNotFoundException($exception, $request, $options): bool
    {
        return false;
    }

    private function throwCustomException($exception, $options): void
    {
        $body = json_decode($exception->response->body(), true);

        if (Arr::has($body, ['error', 'error_description', 'correlation_id'])) {
            throw new $options(json_encode($body), 401);
        }

        throw new $options(json_encode($body['error']), 401);
    }

    private function handleTooManyRequestsException($exception, $request, $options): bool
    {
        $retryAfter = $exception->response->header('Retry-After');
        sleep(empty($retryAfter) ? 10 : $retryAfter);

        return true;
    }

    private function defaultHandler($exception, $request, $options): bool
    {
        return true;
    }
}
