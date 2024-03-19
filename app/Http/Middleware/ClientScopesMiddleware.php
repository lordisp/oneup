<?php

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use App\Models\Passport\Client;
use Closure;
use Illuminate\Http\Request;

class ClientScopesMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isClientCredentialsGrant($request)) {
            $clientScopes = $this->validateClientScopes($request);
            if (count($clientScopes) > 0) {
                $request->request->set('scope', implode(' ', $clientScopes));

                return $next($request);
            }
        }

        return response('Invalid scope(s) provided', 403);
    }

    protected function validateClientScopes(Request $request): array
    {
        $scopes = $request->request->get('scope') === '*'
            ? $this->getAllApprovedScopesFromCurrentClient($request)
            : $this->getScopesFromRequest($request);

        return ! empty($scopes)
            ? Client::whereId($request->get('client_id'))
                ->whereRelation('approvedClientScopes', function ($query) use ($scopes) {
                    foreach ($scopes as $scope) {
                        $query->where('scope', $scope);
                    }
                })
                ->first()
                ->approvedClientScopes
                ->pluck('scope')
                ->toArray()
            : [];
    }

    protected function isClientCredentialsGrant(Request $request): bool
    {
        return $request->has('grant_type')
            && $request->request->get('grant_type') === 'client_credentials'
            && $request->request->has('scope');
    }

    protected function getAllApprovedScopesFromCurrentClient(Request $request): array
    {
        return Client::whereId($request->get('client_id'))
            ->first()
            ->approvedClientScopes
            ->pluck('scope')
            ->toArray();
    }

    protected function getScopesFromRequest($request): array
    {
        return explode(' ', $request->request->get('scope'));
    }
}
