<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\AddressController;

Route::middleware('group.permission:clients')->group(function () {
    Route::apiResource('addresses', AddressController::class);
});
