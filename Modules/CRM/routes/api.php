<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\Api\ClientController;
use Modules\CRM\Http\Controllers\Api\ContractController;
use Modules\CRM\Http\Controllers\Api\PlanController;

Route::middleware('group.permission:clients')->group(function () {
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/{client}/addresses', [ClientController::class, 'addresses'])->name('clients.addresses');
});

Route::middleware('group.permission:plans')->group(function () {
    Route::apiResource('plans', PlanController::class);
});

Route::middleware('group.permission:contracts')->group(function () {
    Route::apiResource('contracts', ContractController::class);
});
