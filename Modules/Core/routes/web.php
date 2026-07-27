<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Core\Http\Controllers\Web\UserController;
use Modules\Core\Http\Controllers\Web\UserGroupController;
use Modules\Core\Http\Controllers\Web\SystemSettingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cores', CoreController::class)->names('core');

    Route::prefix('configuracoes')->name('core.settings.')->group(function () {
        Route::get('/', [SystemSettingController::class, 'index'])->name('index');
        Route::get('/criar', [SystemSettingController::class, 'create'])->name('create');
        Route::post('/', [SystemSettingController::class, 'store'])->name('store');
        Route::put('/', [SystemSettingController::class, 'update'])->name('update');
    });

    Route::middleware('group.permission:settings')->group(function () {
        Route::prefix('usuarios')->name('core.users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/criar', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('grupos')->name('core.user-groups.')->group(function () {
            Route::get('/', [UserGroupController::class, 'index'])->name('index');
            Route::get('/criar', [UserGroupController::class, 'create'])->name('create');
            Route::post('/', [UserGroupController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [UserGroupController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserGroupController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserGroupController::class, 'destroy'])->name('destroy');
        });
    });
});
