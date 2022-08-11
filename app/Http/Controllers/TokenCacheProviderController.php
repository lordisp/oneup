<?php

namespace App\Http\Controllers;

use App\Http\Requests\V1\TokenCacheProviderRequest;
use App\Http\Resources\V1\TokenCacheProvider\TokenCacheProviderShowResource;
use App\Http\Resources\V1\TokenCacheProvider\TokenCacheResource as TokenCacheResourceAlias;
use App\Models\TokenCacheProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenCacheProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return TokenCacheResourceAlias
     */
    public function index(Request $request)
    {
        return new TokenCacheResourceAlias(TokenCacheProvider::paginate((int)$request->input('top', 999)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TokenCacheProviderRequest $request
     * @param TokenCacheProvider $provider
     * @return JsonResponse
     */
    public function store(TokenCacheProviderRequest $request, TokenCacheProvider $provider)
    {
        $provider->create($request->validated());

        return response()->json([
            'status' => 'success'
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $tokencacheprovider
     * @param TokenCacheProvider $provider
     * @return TokenCacheProviderShowResource
     */
    public function show($tokencacheprovider, TokenCacheProvider $provider)
    {
        return new TokenCacheProviderShowResource($provider->whereId($tokencacheprovider)->get());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $tokencacheprovider
     * @param TokenCacheProviderRequest $request
     * @param TokenCacheProvider $provider
     * @return JsonResponse
     */
    public function update($tokencacheprovider, TokenCacheProviderRequest $request, TokenCacheProvider $provider)
    {
        $validated = $request->validated();
        $status = $provider->whereId($tokencacheprovider)->update($validated)
            ? ['message' => 'success', 'code' => 201]
            : ['message' => 'failed', 'code' => 500];

        return response()->json([
            'status' => $status['message']
        ], $status['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\TokenCacheProvider $tokenCacheProvider
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($tokencacheprovider, TokenCacheProvider $tokenCacheProvider)
    {
        $status = $tokenCacheProvider->destroy($tokencacheprovider)
            ? ['message' => 'deleted', 'code' => 200]
            : ['message' => 'failed', 'code' => 500];
        return response()->json([
            'status' => $status['message']
        ], $status['code']);
    }
}
