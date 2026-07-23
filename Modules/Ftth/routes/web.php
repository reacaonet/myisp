<?php

use Illuminate\Support\Facades\Route;
use Modules\Ftth\Http\Controllers\FtthController;

Route::middleware(['auth', 'verified'])->prefix('ftth')->name('ftth.')->group(function () {
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
