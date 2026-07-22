<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Core\Http\Controllers\Web\PermissionController;
use Modules\Core\Http\Controllers\Web\SystemSettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cores', CoreController::class)->names('core');

    Route::prefix('permissoes')->name('core.permissions.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('index');
        Route::get('/{user}/editar', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{user}', [PermissionController::class, 'update'])->name('update');
    });

    Route::prefix('configuracoes')->name('core.settings.')->group(function () {
        Route::get('/', [SystemSettingController::class, 'index'])->name('index');
        Route::get('/criar', [SystemSettingController::class, 'create'])->name('create');
        Route::post('/', [SystemSettingController::class, 'store'])->name('store');
        Route::put('/', [SystemSettingController::class, 'update'])->name('update');
    });
});
