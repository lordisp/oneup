<?php

use App\Http\Controllers\Auth\AuthenticatedOidcController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');

    Route::post('signin', [AuthenticatedOidcController::class, 'signin'])->name('signin');

    Route::get('callback', [AuthenticatedOidcController::class, 'callback'])->name('callback');

    //    Route::post('login', [AuthenticatedSessionController::class, 'store']);

});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedOidcController::class, 'logout'])
        ->name('logout');
});
