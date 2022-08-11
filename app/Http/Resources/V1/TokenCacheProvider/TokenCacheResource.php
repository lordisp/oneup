<?php

namespace App\Http\Resources\V1\TokenCacheProvider;

use App\Http\Resources\Json;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TokenCacheResource extends ResourceCollection
{
    use Json;
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection->map(fn($item) => collect([
                'auth_url' => $item->auth_url,
                'token_url' => $item->token_url,
                'auth_endpoint' => $item->auth_endpoint,
                'client' => $this->json($item->client, 'client_secret'),
            ]))
        ];
    }
}
