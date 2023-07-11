<?php

namespace App\Http\Livewire\DataTable;

use App\Models\Operation;
use Illuminate\Support\Facades\Validator;

trait WithRbacCache
{
    protected function flushRbacCache(): void
    {
        cache()->tags('rbac')->flush();
    }

    protected function updateOrCreate(string $operation, string $description, int|null $ttl = 3600): string
    {
        $attributes = Validator::validate(
            [
                'operation' => $operation,
                'description' => $description,
            ],
            [
                'operation' => 'regex:/^[a-zA-Z]+(?:\/[a-zA-Z]+){1,4}$/',
                'description' => 'required|string|min:4'
            ]
        );

        return cache()->tags('rbac')->remember($attributes['operation'], $ttl, function () use ($attributes) {
            return Operation::updateOrCreate(
                ['operation' => $attributes['operation']],
                ['description' => $attributes['description']]
            )->operation;
        });
    }
}