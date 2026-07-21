<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\Api\InvoiceController;
use Modules\Billing\Http\Controllers\Api\PaymentController;

Route::apiResource('invoices', InvoiceController::class);
Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show', 'destroy']);
