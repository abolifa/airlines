<?php

use App\Http\Controllers\VidecomGatewayController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/videcom/command', [VidecomGatewayController::class, 'command']);
