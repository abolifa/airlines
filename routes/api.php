<?php

use App\Http\Controllers\VidecomGatewayController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/videcom/command', [VidecomGatewayController::class, 'command']);
Route::post('/v1/videcom/history', [VidecomGatewayController::class, 'history']);
Route::post('/v1/videcom/reset', [VidecomGatewayController::class, 'reset']);
