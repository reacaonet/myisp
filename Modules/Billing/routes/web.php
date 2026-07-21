<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\Web\InvoiceController;

Route::prefix('faturas')->name('billing.invoices.')->middleware('auth')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/create', [InvoiceController::class, 'create'])->name('create');
    Route::post('/', [InvoiceController::class, 'store'])->name('store');
    Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
    Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update');
    Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');
    Route::post('/{invoice}/payment', [InvoiceController::class, 'registerPayment'])->name('payment');
    Route::post('/gerar', [InvoiceController::class, 'generateFromContracts'])->name('generate');
    Route::get('/{invoice}/recibo', [InvoiceController::class, 'receipt'])->name('receipt');
});
