<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\Api\ClientController;
use Modules\CRM\Http\Controllers\Api\PlanController;
use Modules\CRM\Http\Controllers\Api\ContractController;

Route::apiResource('clients', ClientController::class);
Route::get('clients/{client}/addresses', [ClientController::class, 'addresses'])->name('clients.addresses');

Route::apiResource('plans', PlanController::class);

Route::apiResource('contracts', ContractController::class);
