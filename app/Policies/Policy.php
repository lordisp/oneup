<?php

namespace App\Policies;

use Illuminate\Support\Facades\Gate;

class Policy
{
    public const operations = [
        'admin/rbac/operations/read',
        'admin/rbac/operations/create',
        'admin/rbac/operations/update',
        'admin/rbac/operations/delete',
        'admin/rbac/operations/restore',
        'admin/rbac/operations/forceDelete',

    ];

    static function gateDenies(string $ability, $arguments = []): void
    {
        if (Gate::denies($ability, auth()->user())) abort(403);
    }

    static function gateAnyDenies(array $abilities): void
    {
        if (Gate::any($abilities, auth()->user())) abort(403);
    }
}