<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\Api\InvoiceController;
use Modules\Billing\Http\Controllers\Api\PaymentController;

Route::middleware('group.permission:invoices')->group(function () {
    Route::apiResource('invoices', InvoiceController::class);
});

Route::middleware('group.permission:invoices')->group(function () {
    Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show', 'destroy']);
});
