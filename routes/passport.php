<?php

use Illuminate\Support\Facades\Route;

Route::name('passport.')->prefix(config('passport.path', 'oauth'))->namespace('Laravel\Passport\Http\Controllers')->group(function () {

    Route::post('/token', 'AccessTokenController@issueToken')->name('token')->middleware('throttle', 'client.scope');

    Route::get('/authorize', 'AuthorizationController@authorize')->name('authorizations.authorize')->middleware('web');

    $guard = config('passport.guard', null);

    Route::middleware(['web', $guard ? 'auth:'.$guard : 'auth'])->group(function () {
        Route::post('/token/refresh', 'TransientTokenController@refresh')->name('token.refresh');

        Route::post('/authorize', 'ApproveAuthorizationController@approve')->name('authorizations.approve');

        Route::delete('/authorize', 'DenyAuthorizationController@deny')->name('authorizations.deny');

        Route::get('/tokens', 'AuthorizedAccessTokenController@forUser')->name('tokens.index');

        Route::delete('/tokens/{token_id}', 'AuthorizedAccessTokenController@destroy')->name('tokens.destroy');

        Route::get('/clients', 'ClientController@forUser')->name('clients.index');

        Route::post('/clients', 'ClientController@store')->name('clients.store');

        Route::put('/clients/{client_id}', 'ClientController@update')->name('clients.update');

        Route::delete('/clients/{client_id}', 'ClientController@destroy')->name('clients.destroy');

        Route::get('/scopes', 'ScopeController@all')->name('scopes.index');

        Route::get('/personal-access-tokens', 'PersonalAccessTokenController@forUser')->name('personal.tokens.index');

        Route::post('/personal-access-tokens', 'PersonalAccessTokenController@store')->name('personal.tokens.store');

        Route::delete('/personal-access-tokens/{token_id}', 'PersonalAccessTokenController@destroy')->name('personal.tokens.destroy');
    });

});
