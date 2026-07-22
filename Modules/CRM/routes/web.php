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
use Modules\CRM\Http\Controllers\Web\EquipmentController;
use Modules\CRM\Http\Controllers\Web\ManufacturerController;

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

    Route::resource('technicians', TechnicianController::class)
        ->names('crm.technicians');

    Route::resource('equipment', EquipmentController::class)
        ->names('crm.equipment');

    Route::resource('manufacturers', ManufacturerController::class)
        ->names('crm.manufacturers')
        ->except('show');

    Route::prefix('tickets')->name('crm.tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/status', [TicketController::class, 'updateStatus'])->name('status');
        Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('destroy');
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
        Route::get('perfil', [TechnicianPortalController::class, 'profile'])->name('profile');
        Route::post('perfil', [TechnicianPortalController::class, 'updateProfile'])->name('profile.update');
        Route::post('perfil/senha', [TechnicianPortalController::class, 'changePassword'])->name('profile.password');
    });
});
