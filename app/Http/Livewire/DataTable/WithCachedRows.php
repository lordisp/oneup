<?php

namespace App\Http\Livewire\DataTable;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait WithCachedRows
{
    protected bool $useCache = false;

    public function useCachedRows(): void
    {
        $this->useCache = true;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function cache($callback)
    {
        $cacheKey = $this->id;

        if ($this->useCache && cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }

        $result = $callback();

        cache()->put($cacheKey, $result);

        return $result;
    }
}
