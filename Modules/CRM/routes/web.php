<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\Web\DashboardController;
use Modules\CRM\Http\Controllers\Web\ClientController;
use Modules\CRM\Http\Controllers\Web\PlanController;
use Modules\CRM\Http\Controllers\Web\ContractController;
use Modules\CRM\Http\Controllers\Web\ServiceOrderController;
use Modules\CRM\Http\Controllers\Web\TechnicianController;
use Modules\CRM\Http\Controllers\Web\PortalController;

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
});

Route::prefix('crm/portal')->name('crm.portal.')->group(function () {
    Route::get('login', [PortalController::class, 'loginForm'])->name('login');
    Route::post('login', [PortalController::class, 'login']);
    Route::post('logout', [PortalController::class, 'logout'])->name('logout');
    Route::get('dashboard', [PortalController::class, 'dashboard'])->name('dashboard')->middleware('auth:client');
});
