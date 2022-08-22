<?php

use App\Http\Controllers\TokenCacheProviderController;
use App\Http\Controllers\V1\Rbac\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
#Todo protect API
Route::middleware(['client'])->group(function () {
    Route::apiResources([
        '/users' => UserController::class,
        'tokencacheprovider' => TokenCacheProviderController::class
    ]);

    Route::get('/groups', function (Request $request) {
        return $request->user()->groups();
    });
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
