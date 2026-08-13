<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\OperatorController;
use App\Http\Controllers\Operator\LqController;
use App\Http\Controllers\Operator\SsController;
use App\Http\Controllers\Operator\TipologiController;
use App\Http\Controllers\Operator\KlassenController;
use App\Http\Controllers\Operator\ProjectController;
use App\Http\Controllers\Operator\CapexController;
use App\Http\Controllers\Operator\ProfitLossController;
use App\Http\Controllers\Operator\CashFlowController;

Route::middleware([
    'auth',
    'role:operator',
])
    ->prefix('operator')
    ->name('operator.')
    ->group(function () {
        Route::get('/dashboard', [OperatorController::class, 'selection'])->name('dashboard');
        Route::get('/potensi-unggulan', [OperatorController::class, 'index'])->name('potensi-unggulan');
        Route::get('/data-investasi', [\App\Http\Controllers\Operator\OperatorDataInvestasiController::class, 'index'])->name('data-investasi.index');
        Route::get('/pdrb', [OperatorController::class, 'pdrbIndex'])->name('pdrb.index');
        Route::get('/pdb-nasional', [OperatorController::class, 'pdbNasionalIndex'])->name('pdb-nasional.index');
        Route::get('/pdb-nasional/detail/{tahun}', [OperatorController::class, 'pdbNasionalDetail'])->name('pdb-nasional.detail');
        
        // Modul PDRB Provinsi Operator
        Route::get('/pdrb-provinsi', [OperatorController::class, 'pdrbProvinsiIndex'])->name('pdrb-provinsi.index');
        Route::post('/pdrb-provinsi/init', [OperatorController::class, 'initPdrbProvinsi'])->name('pdrb-provinsi.init');
        Route::get('/pdrb-provinsi/entry/{provinsi_id}/{tahun}', [OperatorController::class, 'entryPdrbProvinsi'])->name('pdrb-provinsi.entry');
        Route::post('/pdrb-provinsi/save-entry', [OperatorController::class, 'saveEntryPdrbProvinsi'])->name('pdrb-provinsi.save-entry');
        Route::delete('/pdrb-provinsi/group/{provinsi_id}/{tahun}', [OperatorController::class, 'destroyGroupPdrbProvinsi'])->name('pdrb-provinsi.destroy-group');

        Route::post('/pdrb/init', [OperatorController::class, 'initPdrb'])->name('pdrb.init');
        Route::get('/pdrb/entry/{kabupaten_id}/{tahun}', [OperatorController::class, 'entryPdrb'])->name('pdrb.entry');
        Route::post('/pdrb/save-entry', [OperatorController::class, 'saveEntryPdrb'])->name('pdrb.save-entry');
        Route::delete('/pdrb/group/{kabupaten_id}/{tahun}', [OperatorController::class, 'destroyGroupPdrb'])->name('pdrb.destroy-group');
        Route::delete('/pdrb/{id}', [OperatorController::class, 'destroyPdrb'])->name('pdrb.destroy');
        
        // Modul Peluang Investasi (IPRO Engine)
        Route::get('/peluang-investasi', [ProjectController::class, 'dashboard'])->name('peluang-investasi');
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

        // Rute Estimasi CAPEX
        Route::get('/projects/{project}/capex', [CapexController::class, 'index'])->name('projects.capex.index');
        Route::post('/projects/{project}/capex', [CapexController::class, 'store'])->name('projects.capex.store');

        // Rute Proyeksi Laba Rugi (P&L)
        Route::get('/projects/{project}/laba-rugi', [ProfitLossController::class, 'index'])->name('projects.pl.index');
        Route::post('/projects/{project}/laba-rugi/settings', [ProfitLossController::class, 'updateSettings'])->name('projects.pl.settings');
        Route::post('/projects/{project}/laba-rugi/data', [ProfitLossController::class, 'store'])->name('projects.pl.store');

        // Rute Tabel Arus Kas (Cash Flow)
        Route::get('/projects/{project}/cashflow', [CashFlowController::class, 'index'])->name('projects.cashflow.index');
        Route::post('/projects/{project}/cashflow/settings', [CashFlowController::class, 'updateSettings'])->name('projects.cashflow.settings');

        Route::get('/aktivitas', [OperatorController::class, 'aktivitas'])->name('aktivitas');
        
        Route::get('/profile', [OperatorController::class, 'profile'])->name('profile');
        Route::post('/profile', [OperatorController::class, 'updateProfile'])->name('profile.update');
        
        Route::get('/settings', [OperatorController::class, 'settings'])->name('settings');
        Route::post('/settings/password', [OperatorController::class, 'updatePassword'])->name('settings.password');
        Route::post('/settings/2fa', [OperatorController::class, 'toggle2FA'])->name('settings.2fa');

        // Analisis LQ Routes
        Route::get('/analisis-lq', [LqController::class, 'index'])->name('lq.index');
        Route::get('/analisis-lq/detail', [LqController::class, 'show'])->name('lq.show');
        Route::post('/analisis-lq/hitung', [LqController::class, 'store'])->name('lq.store');
        Route::delete('/analisis-lq/empty', [LqController::class, 'empty'])->name('lq.empty');
        Route::delete('/analisis-lq/bulk-delete', [LqController::class, 'bulkDestroy'])->name('lq.bulkDestroy');
        Route::put('/analisis-lq/{id}', [LqController::class, 'update'])->name('lq.update');
        Route::delete('/analisis-lq/{id}', [LqController::class, 'destroy'])->name('lq.destroy');
        Route::post('/analisis-lq/import', [LqController::class, 'import'])->name('lq.import');

        // Analisis SS Routes
        Route::get('/analisis-ss', [SsController::class, 'index'])->name('ss.index');
        Route::get('/analisis-ss/detail', [SsController::class, 'show'])->name('ss.show');
        Route::post('/analisis-ss/hitung', [SsController::class, 'store'])->name('ss.store');
        Route::delete('/analisis-ss/empty', [SsController::class, 'empty'])->name('ss.empty');
        Route::delete('/analisis-ss/bulk-delete', [SsController::class, 'bulkDestroy'])->name('ss.bulkDestroy');
        Route::put('/analisis-ss/{id}', [SsController::class, 'update'])->name('ss.update');
        Route::delete('/analisis-ss/{id}', [SsController::class, 'destroy'])->name('ss.destroy');
        Route::post('/analisis-ss/import', [SsController::class, 'import'])->name('ss.import');

        // Analisis Tipologi Routes
        Route::get('/analisis-tipologi', [TipologiController::class, 'index'])->name('tipologi.index');
        Route::get('/analisis-tipologi/detail', [TipologiController::class, 'show'])->name('tipologi.show');
        Route::post('/analisis-tipologi/hitung', [TipologiController::class, 'store'])->name('tipologi.store');
        Route::delete('/analisis-tipologi/empty', [TipologiController::class, 'empty'])->name('tipologi.empty');
        Route::delete('/analisis-tipologi/bulk-delete', [TipologiController::class, 'bulkDestroy'])->name('tipologi.bulkDestroy');
        Route::put('/analisis-tipologi/{id}', [TipologiController::class, 'update'])->name('tipologi.update');
        Route::delete('/analisis-tipologi/{id}', [TipologiController::class, 'destroy'])->name('tipologi.destroy');
        Route::post('/analisis-tipologi/import', [TipologiController::class, 'import'])->name('tipologi.import');
        Route::post('/analisis-tipologi/sync', [TipologiController::class, 'syncFromDatabase'])->name('tipologi.sync');

        // Analisis Klassen Routes
        Route::get('/analisis-klassen', [KlassenController::class, 'index'])->name('klassen.index');
        Route::get('/analisis-klassen/detail', [KlassenController::class, 'show'])->name('klassen.show');
        Route::post('/analisis-klassen/hitung', [KlassenController::class, 'store'])->name('klassen.store');
        Route::delete('/analisis-klassen/empty', [KlassenController::class, 'empty'])->name('klassen.empty');
        Route::delete('/analisis-klassen/bulk-delete', [KlassenController::class, 'bulkDestroy'])->name('klassen.bulkDestroy');
        Route::put('/analisis-klassen/{id}', [KlassenController::class, 'update'])->name('klassen.update');
        Route::delete('/analisis-klassen/{id}', [KlassenController::class, 'destroy'])->name('klassen.destroy');
        Route::post('/analisis-klassen/import', [KlassenController::class, 'import'])->name('klassen.import');
        Route::post('/analisis-klassen/sync', [KlassenController::class, 'syncFromDatabase'])->name('klassen.sync');
    });
