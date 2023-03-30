<?php

use App\Http\Controllers\V1\SubnetController;
use Illuminate\Support\Facades\Route;

Route::get('subnets', [SubnetController::class, 'index'])
    ->middleware(['client:subnets-read']);

Route::post('subnets', [SubnetController::class, 'store'])
    ->middleware(['client:subnets-create']);

Route::put('subnets/{id}', [SubnetController::class, 'update'])
    ->middleware(['client:subnets-update']);

Route::delete('subnets/{id}', [SubnetController::class, 'destroy'])
    ->middleware(['client:subnets-delete']);