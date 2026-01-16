<?php

use App\Http\Controllers\VidecomGatewayController;

Route::post('/v1/videcom/command', [VidecomGatewayController::class, 'command']);
