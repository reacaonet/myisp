<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\Web\InvoiceController;
use Modules\Billing\Http\Controllers\Web\CashBookController;
use Modules\Billing\Http\Controllers\Web\ReportController;
use Modules\Billing\Http\Controllers\Web\BoletoController;
use Modules\Billing\Http\Controllers\Web\PaymentGatewayController;
use Modules\Billing\Http\Controllers\Web\WebhookController;

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
    Route::post('/{invoice}/block', [InvoiceController::class, 'block'])->name('block');
    Route::post('/{invoice}/unblock', [InvoiceController::class, 'unblock'])->name('unblock');
});

Route::prefix('livro-caixa')->name('billing.cash-book.')->middleware('auth')->group(function () {
    Route::get('/', [CashBookController::class, 'index'])->name('index');
    Route::get('/create', [CashBookController::class, 'create'])->name('create');
    Route::post('/', [CashBookController::class, 'store'])->name('store');
    Route::get('/{entry}', [CashBookController::class, 'show'])->name('show');
    Route::get('/{entry}/edit', [CashBookController::class, 'edit'])->name('edit');
    Route::put('/{entry}', [CashBookController::class, 'update'])->name('update');
    Route::delete('/{entry}', [CashBookController::class, 'destroy'])->name('destroy');
});

Route::prefix('relatorios')->name('billing.reports.')->middleware('auth')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/faturas-vencimento', [ReportController::class, 'invoicesByDueDate'])->name('invoices-by-due-date');
    Route::get('/faturas-status', [ReportController::class, 'invoicesByStatus'])->name('invoices-by-status');
    Route::get('/assinantes', [ReportController::class, 'subscribers'])->name('subscribers');
    Route::get('/planos-clientes', [ReportController::class, 'plansVsClients'])->name('plans-vs-clients');
    Route::get('/movimento-caixa', [ReportController::class, 'cashFlow'])->name('cash-flow');
});

Route::prefix('boletos')->name('billing.boleto.')->middleware('auth')->group(function () {
    Route::get('/', [BoletoController::class, 'index'])->name('index');
    Route::get('/{invoice}/imprimir', [BoletoController::class, 'print'])->name('print');
    Route::post('/{invoice}/gerar-boleto', [BoletoController::class, 'generateBoleto'])->name('generate-boleto');
    Route::post('/{invoice}/gerar-pix', [BoletoController::class, 'generatePix'])->name('generate-pix');
    Route::get('/{invoice}/sincronizar-status', [BoletoController::class, 'refreshStatus'])->name('refresh-status');
    Route::post('/{invoice}/cancelar-pagamento', [BoletoController::class, 'cancelPayment'])->name('cancel-payment');
    Route::post('/{invoice}/excluir-pagamento', [BoletoController::class, 'deletePayment'])->name('delete-payment');
});

Route::prefix('gateways')->name('billing.gateways.')->middleware('auth')->group(function () {
    Route::get('/', [PaymentGatewayController::class, 'index'])->name('index');
    Route::get('/criar', [PaymentGatewayController::class, 'create'])->name('create');
    Route::post('/', [PaymentGatewayController::class, 'store'])->name('store');
    Route::get('/{gateway}/editar', [PaymentGatewayController::class, 'edit'])->name('edit');
    Route::put('/{gateway}', [PaymentGatewayController::class, 'update'])->name('update');
    Route::delete('/{gateway}', [PaymentGatewayController::class, 'destroy'])->name('destroy');
    Route::get('/{gateway}/test', [PaymentGatewayController::class, 'test'])->name('test');
});

Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago'])->name('billing.webhooks.mercadopago');
