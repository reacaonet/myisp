<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\Web\BlockPageController;
use Modules\Core\Http\Controllers\Web\LandingController;
use Modules\Core\Http\Controllers\Web\NoticePageController;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('definir-senha', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('definir-senha', [PasswordController::class, 'update'])->name('password.update');
});

Route::get('/', [LandingController::class, 'index'])->name('landing.index');
Route::get('/investidores', [LandingController::class, 'investors'])->name('landing.investors');
Route::get('/sac', [LandingController::class, 'sac'])->name('landing.sac');

Route::get('/bloqueio', [BlockPageController::class, 'show'])->name('block.page');
Route::get('/aviso', [NoticePageController::class, 'show'])->name('notice.page');
