<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\CoreController;
use Modules\Core\Http\Controllers\Web\BranchController;
use Modules\Core\Http\Controllers\Web\CompanyController;
use Modules\Core\Http\Controllers\Web\ContextController;
use Modules\Core\Http\Controllers\Web\FranchiseeController;
use Modules\Core\Http\Controllers\Web\LandingBannerController;
use Modules\Core\Http\Controllers\Web\ProfileController;
use Modules\Core\Http\Controllers\Web\SystemSettingController;
use Modules\Core\Http\Controllers\Web\UserController;
use Modules\Core\Http\Controllers\Web\UserGroupController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cores', CoreController::class)->names('core');

    Route::post('/contexto', [ContextController::class, 'switch'])->name('core.context.switch');

    // Fora do bloco `group.permission:settings` de proposito: perfil e da conta
    // logada, nao do area de administracao. Um tecnico sem permissao de
    // `settings` precisa poder corrigir o proprio telefone.
    Route::prefix('perfil')->name('core.profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    // Painel do franqueado. O isolamento nao vem daqui: vem do vinculo em
    // company_user, resolvido pelo TenantContext a cada consulta.
    Route::prefix('franqueado')->name('core.franchisee.')->middleware('group.permission:franchisees')->group(function () {
        Route::get('/', [FranchiseeController::class, 'index'])->name('index');
        Route::get('/clientes', [FranchiseeController::class, 'clients'])->name('clients');
    });

    Route::prefix('configuracoes')->name('core.settings.')->middleware('group.permission:settings')->group(function () {
        Route::get('/', [SystemSettingController::class, 'index'])->name('index');
        Route::get('/criar', [SystemSettingController::class, 'create'])->name('create');
        Route::post('/', [SystemSettingController::class, 'store'])->name('store');
        Route::put('/', [SystemSettingController::class, 'update'])->name('update');
    });

    Route::prefix('banners')->name('core.banners.')->middleware('group.permission:settings')->group(function () {
        Route::get('/', [LandingBannerController::class, 'index'])->name('index');
        Route::get('/criar', [LandingBannerController::class, 'create'])->name('create');
        Route::post('/', [LandingBannerController::class, 'store'])->name('store');
        Route::get('/{id}/editar', [LandingBannerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LandingBannerController::class, 'update'])->name('update');
        Route::post('/{id}/mover/{direction}', [LandingBannerController::class, 'move'])->name('move');
        Route::delete('/{id}', [LandingBannerController::class, 'destroy'])->name('destroy');
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

        Route::prefix('companias')->name('core.companies.')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/criar', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{id}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{id}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::get('/{company}/convidar-admin', [CompanyController::class, 'inviteAdminForm'])->name('admin.form');
            Route::post('/{company}/convidar-admin', [CompanyController::class, 'inviteAdmin'])->name('admin.store');
        });

        Route::prefix('filiais')->name('core.branches.')->group(function () {
            Route::get('/', [BranchController::class, 'index'])->name('index');
            Route::get('/criar', [BranchController::class, 'create'])->name('create');
            Route::post('/', [BranchController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [BranchController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BranchController::class, 'update'])->name('update');
            Route::delete('/{id}', [BranchController::class, 'destroy'])->name('destroy');
        });
    });
});
