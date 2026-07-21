<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Api\AddressController;

Route::apiResource('addresses', AddressController::class);
