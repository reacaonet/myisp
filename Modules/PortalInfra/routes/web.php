<?php

use Illuminate\Support\Facades\Route;
use Modules\PortalInfra\Http\Controllers\Web\MikrotikServerController;
use Modules\PortalInfra\Http\Controllers\Web\MikrotikController;
use Modules\PortalInfra\Http\Controllers\Web\MikrotikScriptController;
use Modules\PortalInfra\Http\Controllers\Web\MikrotikBackupController;
use Modules\PortalInfra\Http\Controllers\Web\IpPoolController;
use Modules\PortalInfra\Http\Controllers\Web\FirewallController;
use Modules\PortalInfra\Http\Controllers\Web\InterfaceController;
use Modules\PortalInfra\Http\Controllers\Web\ArpController;
use Modules\PortalInfra\Http\Controllers\Web\LogsController;
use Modules\PortalInfra\Http\Controllers\Web\ProvisionController;
use Modules\PortalInfra\Http\Controllers\Web\UptimeController;
use Modules\PortalInfra\Http\Controllers\Web\NetworkMonitorController;
use Modules\PortalInfra\Http\Controllers\Web\SiteBlockingController;
use Modules\PortalInfra\Http\Controllers\Web\EquipmentController;
use Modules\PortalInfra\Http\Controllers\Web\ManufacturerController;
use Modules\PortalInfra\Http\Controllers\Web\HotspotCouponController;
use Modules\PortalInfra\Http\Controllers\Web\FtthController;
use Modules\PortalInfra\Http\Controllers\Web\InfraLoginController;

Route::prefix('infra')->name('infra.')->group(function () {

    // Login (guest only)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [InfraLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [InfraLoginController::class, 'login']);
    });

    // Logout
    Route::post('/logout', [InfraLoginController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', function () {
        return view('infra::dashboard');
    })->name('dashboard');

    // ==================== MikroTik ====================

    Route::resource('mikrotik-servers', MikrotikServerController::class)
        ->names('mikrotik-servers');
    Route::post('mikrotik-servers/{mikrotik_server}/test', [MikrotikServerController::class, 'testConnection'])
        ->name('mikrotik-servers.test');

    Route::prefix('mikrotik')->name('mikrotik.')->group(function () {
        Route::get('/pppoe-ativos', [MikrotikController::class, 'pppoeActive'])->name('pppoe-active');
        Route::post('/pppoe-ativos/{serverId}/kick', [MikrotikController::class, 'kickPppoe'])->name('kick-pppoe');
        Route::get('/hotspot-ativos', [MikrotikController::class, 'hotspotActive'])->name('hotspot-active');
        Route::post('/hotspot-ativos/{serverId}/kick', [MikrotikController::class, 'kickHotspot'])->name('kick-hotspot');
        Route::get('/ip-pools', [IpPoolController::class, 'index'])->name('ip-pools');
        Route::post('/ip-pools', [IpPoolController::class, 'store'])->name('ip-pools.store');
        Route::delete('/ip-pools/{serverId}', [IpPoolController::class, 'destroy'])->name('ip-pools.destroy');
        Route::get('/nat-rules', [FirewallController::class, 'natRules'])->name('nat-rules');
        Route::get('/address-list', [FirewallController::class, 'addressList'])->name('address-list');
        Route::get('/logs', [LogsController::class, 'index'])->name('logs');
        Route::get('/interfaces', [InterfaceController::class, 'index'])->name('interfaces');
        Route::get('/arp', [ArpController::class, 'index'])->name('arp');
        Route::get('/scripts', [MikrotikScriptController::class, 'index'])->name('scripts');
        Route::post('/scripts/gerar', [MikrotikScriptController::class, 'generate'])->name('scripts.generate');
    });

    Route::resource('mikrotik-backups', MikrotikBackupController::class)
        ->names('mikrotik-backups');
    Route::get('mikrotik-backups/{backup}/download', [MikrotikBackupController::class, 'download'])
        ->name('mikrotik-backups.download');

    Route::prefix('provisioning')->name('provisioning.')->group(function () {
        Route::get('/', [ProvisionController::class, 'index'])->name('index');
        Route::get('/create', [ProvisionController::class, 'create'])->name('create');
        Route::post('/', [ProvisionController::class, 'store'])->name('store');
        Route::delete('/{id}', [ProvisionController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/block', [ProvisionController::class, 'block'])->name('block');
        Route::get('/profiles/{server_id}', [ProvisionController::class, 'profiles'])->name('profiles');
        Route::get('/active-users/{server_id}', [ProvisionController::class, 'activeUsers'])->name('active-users');
    });

    Route::resource('hotspot-coupons', HotspotCouponController::class)
        ->names('hotspot-coupons');
    Route::post('hotspot-coupons/generate-batch', [HotspotCouponController::class, 'generateBatch'])
        ->name('hotspot-coupons.generate-batch');

    // ==================== Monitoramento ====================

    Route::prefix('uptime')->name('uptime.')->group(function () {
        Route::get('/', [UptimeController::class, 'index'])->name('index');
        Route::get('/create', [UptimeController::class, 'create'])->name('create');
        Route::post('/', [UptimeController::class, 'store'])->name('store');
        Route::get('/{monitor}', [UptimeController::class, 'show'])->name('show');
        Route::get('/{monitor}/edit', [UptimeController::class, 'edit'])->name('edit');
        Route::put('/{monitor}', [UptimeController::class, 'update'])->name('update');
        Route::delete('/{monitor}', [UptimeController::class, 'destroy'])->name('destroy');
        Route::post('/{monitor}/check', [UptimeController::class, 'check'])->name('check');
        Route::post('/check-all', [UptimeController::class, 'checkAll'])->name('check-all');
    });

    Route::prefix('network-monitor')->name('network-monitor.')->group(function () {
        Route::get('/', [NetworkMonitorController::class, 'index'])->name('index');
        Route::get('/{id}', [NetworkMonitorController::class, 'show'])->name('show');
        Route::get('/{id}/active-users', [NetworkMonitorController::class, 'activeUsers'])->name('active-users');
        Route::get('/{id}/refresh', [NetworkMonitorController::class, 'refreshStats'])->name('refresh');
    });

    Route::prefix('site-blocking')->name('site-blocking.')->group(function () {
        Route::get('/', [SiteBlockingController::class, 'index'])->name('index');
        Route::post('/block', [SiteBlockingController::class, 'block'])->name('block');
        Route::post('/unblock', [SiteBlockingController::class, 'unblock'])->name('unblock');
    });

    // ==================== Equipamentos ====================

    Route::resource('equipment', EquipmentController::class)
        ->names('equipment');

    Route::resource('manufacturers', ManufacturerController::class)
        ->names('manufacturers')
        ->except('show');

    // ==================== FTTH ====================

    Route::prefix('ftth')->name('ftth.')->group(function () {
        Route::get('/', [FtthController::class, 'dashboard'])->name('dashboard');

        Route::get('/ctos', [FtthController::class, 'indexCtos'])->name('ctos.index');
        Route::get('/ctos/criar', [FtthController::class, 'createCto'])->name('ctos.create');
        Route::post('/ctos', [FtthController::class, 'storeCto'])->name('ctos.store');
        Route::get('/ctos/{id}', [FtthController::class, 'showCto'])->name('ctos.show');
        Route::get('/ctos/{id}/editar', [FtthController::class, 'editCto'])->name('ctos.edit');
        Route::put('/ctos/{id}', [FtthController::class, 'updateCto'])->name('ctos.update');
        Route::delete('/ctos/{id}', [FtthController::class, 'destroyCto'])->name('ctos.destroy');

        Route::get('/caixas', [FtthController::class, 'indexCaixas'])->name('caixas.index');
        Route::get('/caixas/criar', [FtthController::class, 'createCaixa'])->name('caixas.create');
        Route::post('/caixas', [FtthController::class, 'storeCaixa'])->name('caixas.store');
        Route::get('/caixas/{id}', [FtthController::class, 'showCaixa'])->name('caixas.show');
        Route::get('/caixas/{id}/editar', [FtthController::class, 'editCaixa'])->name('caixas.edit');
        Route::put('/caixas/{id}', [FtthController::class, 'updateCaixa'])->name('caixas.update');
        Route::delete('/caixas/{id}', [FtthController::class, 'destroyCaixa'])->name('caixas.destroy');

        Route::get('/gerar', [FtthController::class, 'generateNetwork'])->name('generate');
        Route::post('/gerar', [FtthController::class, 'runGenerate'])->name('generate.run');

        Route::get('/gerar-cidade', [FtthController::class, 'generateCity'])->name('generate.city');
        Route::post('/gerar-cidade', [FtthController::class, 'runGenerateCity'])->name('generate.city.run');

        Route::get('/gerar-cidades', [FtthController::class, 'generateCities'])->name('generate.cities');

        Route::get('/exportar-kml', [FtthController::class, 'exportKml'])->name('export.kml');
        Route::get('/exportar-kml/{city}', [FtthController::class, 'downloadKml'])->name('export.kml.download');

        Route::get('/mapa', [FtthController::class, 'map'])->name('map');
        Route::get('/api/map-data', [FtthController::class, 'mapData'])->name('api.map-data');
    });
    });
});
