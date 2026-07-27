<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\Web\DashboardController;
use Modules\CRM\Http\Controllers\Web\ClientController;
use Modules\CRM\Http\Controllers\Web\PlanController;
use Modules\CRM\Http\Controllers\Web\ContractController;
use Modules\CRM\Http\Controllers\Web\ServiceOrderController;
use Modules\CRM\Http\Controllers\Web\TechnicianController;
use Modules\CRM\Http\Controllers\Web\PortalController;
use Modules\CRM\Http\Controllers\Web\TicketController;
use Modules\CRM\Http\Controllers\Web\TechnicianPortalController;
use Modules\CRM\Http\Controllers\Web\SupplierController;
use Modules\CRM\Http\Controllers\Web\NewsletterController;

Route::prefix('crm')->middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('crm.dashboard');

    Route::resource('clients', ClientController::class)
        ->names('crm.clients')
        ->except('show');

    Route::get('clients/{client}', [ClientController::class, 'show'])->name('crm.clients.show');
    Route::get('clients/{client}/history', [ClientController::class, 'history'])->name('crm.clients.history');

    Route::resource('plans', PlanController::class)
        ->names('crm.plans')
        ->except('show');

    Route::resource('contracts', ContractController::class)
        ->names('crm.contracts')
        ->except('show');

    Route::get('contracts/{contract}', [ContractController::class, 'show'])->name('crm.contracts.show');

    Route::resource('service-orders', ServiceOrderController::class)
        ->names('crm.service-orders')
        ->except('show');

    Route::get('service-orders/{service_order}', [ServiceOrderController::class, 'show'])->name('crm.service-orders.show');
    Route::post('service-orders/{service_order}/iniciar', [ServiceOrderController::class, 'start'])->name('crm.service-orders.start');
    Route::post('service-orders/{service_order}/concluir', [ServiceOrderController::class, 'complete'])->name('crm.service-orders.complete');
    Route::post('service-orders/{service_order}/atribuir', [ServiceOrderController::class, 'assign'])->name('crm.service-orders.assign');

    Route::resource('technicians', TechnicianController::class)
        ->names('crm.technicians');

    Route::resource('suppliers', SupplierController::class)
        ->names('crm.suppliers');

    Route::prefix('tickets')->name('crm.tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/status', [TicketController::class, 'updateStatus'])->name('status');
        Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('newsletter')->name('crm.newsletter.')->group(function () {
        Route::get('/', [NewsletterController::class, 'index'])->name('index');
        Route::post('/send', [NewsletterController::class, 'send'])->name('send');
    });
});

Route::prefix('crm/portal')->name('crm.portal.')->group(function () {
    Route::get('login', [PortalController::class, 'loginForm'])->name('login');
    Route::post('login', [PortalController::class, 'login']);
    Route::post('logout', [PortalController::class, 'logout'])->name('logout');

    Route::middleware('auth:client')->group(function () {
        Route::get('dashboard', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('faturas', [PortalController::class, 'invoices'])->name('invoices');
        Route::get('faturas/{invoice}', [PortalController::class, 'invoiceShow'])->name('invoices.show');
        Route::get('faturas/{invoice}/recibo', [PortalController::class, 'invoiceReceipt'])->name('invoices.receipt');
        Route::get('faturas/{invoice}/pagar', [PortalController::class, 'invoicePaymentForm'])->name('invoices.pay');
        Route::post('faturas/{invoice}/pagar', [PortalController::class, 'invoicePay'])->name('invoices.pay.store');
        Route::get('faturas/{invoice}/boleto', [PortalController::class, 'invoiceBoleto'])->name('invoices.boleto');
        Route::post('faturas/{invoice}/gerar-boleto', [PortalController::class, 'invoiceGenerateBoleto'])->name('invoices.generate-boleto');
        Route::post('faturas/{invoice}/gerar-pix', [PortalController::class, 'invoiceGeneratePix'])->name('invoices.generate-pix');
        Route::post('faturas/{invoice}/cancelar-pagamento', [PortalController::class, 'invoiceCancelBoleto'])->name('invoices.cancel-payment');
        Route::post('faturas/{invoice}/excluir-pagamento', [PortalController::class, 'invoiceDeleteBoleto'])->name('invoices.delete-payment');
        Route::get('contratos', [PortalController::class, 'contracts'])->name('contracts');
        Route::get('contratos/{contract}', [PortalController::class, 'contractShow'])->name('contracts.show');
        Route::get('ordens-servico', [PortalController::class, 'serviceOrders'])->name('service-orders');
        Route::get('perfil', [PortalController::class, 'profile'])->name('profile');
        Route::post('perfil', [PortalController::class, 'updateProfile'])->name('profile.update');
        Route::post('perfil/senha', [PortalController::class, 'changePassword'])->name('profile.password');
        Route::get('chamados', [PortalController::class, 'tickets'])->name('tickets');
        Route::get('chamados/abrir', [PortalController::class, 'ticketCreate'])->name('tickets.create');
        Route::post('chamados', [PortalController::class, 'ticketStore'])->name('tickets.store');
        Route::get('chamados/{ticket}', [PortalController::class, 'ticketShow'])->name('tickets.show');
        Route::post('chamados/{ticket}/responder', [PortalController::class, 'ticketReply'])->name('tickets.reply');
    });
});

Route::prefix('tecnico')->name('technician.portal.')->group(function () {
    Route::get('login', [TechnicianPortalController::class, 'loginForm'])->name('login');
    Route::post('login', [TechnicianPortalController::class, 'login']);
    Route::post('logout', [TechnicianPortalController::class, 'logout'])->name('logout');

    Route::middleware('auth:technician')->group(function () {
        Route::get('dashboard', [TechnicianPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('ordens-servico', [TechnicianPortalController::class, 'serviceOrders'])->name('service-orders');
        Route::get('ordens-servico/{service_order}', [TechnicianPortalController::class, 'serviceOrderShow'])->name('service-orders.show');
        Route::put('ordens-servico/{service_order}', [TechnicianPortalController::class, 'updateServiceOrder'])->name('service-orders.update');
        Route::post('ordens-servico/{service_order}/iniciar', [TechnicianPortalController::class, 'startServiceOrder'])->name('service-orders.start');
        Route::post('ordens-servico/{service_order}/concluir', [TechnicianPortalController::class, 'completeServiceOrder'])->name('service-orders.complete');
        Route::get('perfil', [TechnicianPortalController::class, 'profile'])->name('profile');
        Route::post('perfil', [TechnicianPortalController::class, 'updateProfile'])->name('profile.update');
        Route::post('perfil/senha', [TechnicianPortalController::class, 'changePassword'])->name('profile.password');
    });
});
