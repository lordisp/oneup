<?php

namespace App\Models\Passport;

use App\Models\Scope;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    //use HasFactory;
    public function clientScopes(): BelongsToMany
    {
        return $this->belongsToMany(Scope::class, 'oauth_client_scope', 'oauth_client_id', 'scope_id')
            ->withPivot(['approved_at', 'approved_by']);
    }

    public function approvedClientScopes(): BelongsToMany
    {
        return $this->belongsToMany(Scope::class, 'oauth_client_scope', 'oauth_client_id', 'scope_id')
            ->wherePivotNotNull('approved_by')
            ->wherePivotNotNull('approved_at')
            ->withPivot(['approved_at', 'approved_by']);
    }
}
