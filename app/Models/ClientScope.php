<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @method static whereClientScope(string $clientId, string $scopeId)
 */
class ClientScope extends Pivot
{
    protected $table = 'oauth_client_scope';

    public function scopeWhereClientScope($query, string $clientId, string $scopeId)
    {
        return $query->where('oauth_client_id', $clientId)
            ->where('scope_id', $scopeId);
    }

    public function scopeApproveScope(Builder $query, User $user): int
    {
        return $query->update(['approved_at' => now(), 'approved_by' => $user->email]);
    }
}
