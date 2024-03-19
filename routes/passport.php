<?php

use App\Http\Controllers\Laravel;
use Illuminate\Support\Facades\Route;

Route::name('passport.')->prefix(config('passport.path', 'oauth'))->group(function () {

    Route::post('/token', [Laravel\Passport\Http\Controllers\AccessTokenController::class, 'issueToken'])->name('token')->middleware('throttle', 'client.scope');

    Route::get('/authorize', [Laravel\Passport\Http\Controllers\AuthorizationController::class, 'authorize'])->name('authorizations.authorize')->middleware('web');

    $guard = config('passport.guard', null);

    Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {
        Route::post('/token/refresh', [Laravel\Passport\Http\Controllers\TransientTokenController::class, 'refresh'])->name('token.refresh');

        Route::post('/authorize', [Laravel\Passport\Http\Controllers\ApproveAuthorizationController::class, 'approve'])->name('authorizations.approve');

        Route::delete('/authorize', [Laravel\Passport\Http\Controllers\DenyAuthorizationController::class, 'deny'])->name('authorizations.deny');

        Route::get('/tokens', [Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController::class, 'forUser'])->name('tokens.index');

        Route::delete('/tokens/{token_id}', [Laravel\Passport\Http\Controllers\AuthorizedAccessTokenController::class, 'destroy'])->name('tokens.destroy');

        Route::get('/clients', [Laravel\Passport\Http\Controllers\ClientController::class, 'forUser'])->name('clients.index');

        Route::post('/clients', [Laravel\Passport\Http\Controllers\ClientController::class, 'store'])->name('clients.store');

        Route::put('/clients/{client_id}', [Laravel\Passport\Http\Controllers\ClientController::class, 'update'])->name('clients.update');

        Route::delete('/clients/{client_id}', [Laravel\Passport\Http\Controllers\ClientController::class, 'destroy'])->name('clients.destroy');

        Route::get('/scopes', [Laravel\Passport\Http\Controllers\ScopeController::class, 'all'])->name('scopes.index');

        Route::get('/personal-access-tokens', [Laravel\Passport\Http\Controllers\PersonalAccessTokenController::class, 'forUser'])->name('personal.tokens.index');

        Route::post('/personal-access-tokens', [Laravel\Passport\Http\Controllers\PersonalAccessTokenController::class, 'store'])->name('personal.tokens.store');

        Route::delete('/personal-access-tokens/{token_id}', [Laravel\Passport\Http\Controllers\PersonalAccessTokenController::class, 'destroy'])->name('personal.tokens.destroy');
    });

});
