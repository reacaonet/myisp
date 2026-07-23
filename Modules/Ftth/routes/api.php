<?php

use Illuminate\Support\Facades\Route;
use Modules\Ftth\Http\Controllers\FtthController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('ftths', FtthController::class)->names('ftth');
});
