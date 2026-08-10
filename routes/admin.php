<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\DataWilayahController;
use App\Http\Controllers\Admin\DataKbliController;
use App\Http\Controllers\Admin\DataHsCodeController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\MoneyCurrencyController;
use App\Http\Controllers\Admin\DataKbkiController;
use App\Http\Controllers\Admin\AdminProyekIproController;
use App\Http\Controllers\Admin\AdminPdrbController;
use App\Http\Controllers\Admin\AdminPdbNasionalController;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Review Dokumen Proyek IPRO (CAPEX, P&L, Arus Kas)
        |--------------------------------------------------------------------------
        */
        Route::get('/proyek-ipro', [AdminProyekIproController::class, 'index'])
            ->name('proyek-ipro.index');

        Route::get('/proyek-ipro/{id}', [AdminProyekIproController::class, 'show'])
            ->name('proyek-ipro.show');

        /*
        |--------------------------------------------------------------------------
        | Pengguna
        |--------------------------------------------------------------------------
        */

        Route::get('/pengguna', [PenggunaController::class, 'index'])
            ->name('pengguna.index');

        Route::post('/pengguna', [PenggunaController::class, 'store'])
            ->name('pengguna.store');

        Route::put('/pengguna/{pengguna}', [PenggunaController::class, 'update'])
            ->name('pengguna.update');

        Route::delete('/pengguna/{pengguna}', [PenggunaController::class, 'destroy'])
            ->name('pengguna.destroy');

        /*
        |--------------------------------------------------------------------------
        | Data Wilayah
        |--------------------------------------------------------------------------
        */

        Route::get('/data-wilayah/options/provinsi', [DataWilayahController::class, 'provinceOptions'])
            ->name('data-wilayah.options.provinsi');

        Route::get('/data-wilayah/options/kabupaten', [DataWilayahController::class, 'regencyOptions'])
            ->name('data-wilayah.options.kabupaten');

        Route::get('/data-wilayah/options/kecamatan', [DataWilayahController::class, 'districtOptions'])
            ->name('data-wilayah.options.kecamatan');

        Route::get('/data-wilayah/options/desa', [DataWilayahController::class, 'villageOptions'])
            ->name('data-wilayah.options.desa');

        Route::get('/data-wilayah', [DataWilayahController::class, 'index'])
            ->name('data-wilayah.index');

        Route::post('/data-wilayah', [DataWilayahController::class, 'store'])
            ->name('data-wilayah.store');

        Route::put('/data-wilayah/{dataWilayah}', [DataWilayahController::class, 'update'])
            ->name('data-wilayah.update');

        Route::delete('/data-wilayah/{dataWilayah}', [DataWilayahController::class, 'destroy'])
            ->name('data-wilayah.destroy');


        /*
        |--------------------------------------------------------------------------
        | Data KBLI
        |--------------------------------------------------------------------------
        */

        Route::get('/data-kbli', [DataKbliController::class, 'index'])
            ->name('data-kbli.index');

        Route::post('/data-kbli', [DataKbliController::class, 'store'])
            ->name('data-kbli.store');

        Route::put('/data-kbli/{id}', [DataKbliController::class, 'update'])
            ->name('data-kbli.update');

        Route::delete('/data-kbli/{id}', [DataKbliController::class, 'destroy'])
            ->name('data-kbli.destroy');

        /*
        |--------------------------------------------------------------------------
        | Data KBKI
        |--------------------------------------------------------------------------
        */

        Route::get('/data-kbki', [DataKbkiController::class, 'index'])
            ->name('data-kbki.index');

        Route::post('/data-kbki', [DataKbkiController::class, 'store'])
            ->name('data-kbki.store');

        Route::put('/data-kbki/{id}', [DataKbkiController::class, 'update'])
            ->name('data-kbki.update');

        Route::delete('/data-kbki/{id}', [DataKbkiController::class, 'destroy'])
            ->name('data-kbki.destroy');

        Route::get('/data-kbki/parent-options',[DataKbkiController::class, 'parentOptions'])
            ->name('data-kbki.parent-options');

        /*
        |--------------------------------------------------------------------------
        | HS Code
        |--------------------------------------------------------------------------
        */

        Route::get('/hs-code', [DataHsCodeController::class, 'index'])
            ->name('hs-code.index');

        Route::post('/hs-code', [DataHsCodeController::class, 'store'])
            ->name('hs-code.store');

        Route::put('/hs-code/{id}', [DataHsCodeController::class, 'update'])
            ->name('hs-code.update');

        Route::delete('/hs-code/{id}', [DataHsCodeController::class, 'destroy'])
            ->name('hs-code.destroy');

        /*
        |--------------------------------------------------------------------------
        | Profil Admin
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [AdminProfileController::class, 'index'])
            ->name('profile.index');

        Route::put('/profile', [AdminProfileController::class, 'update'])
            ->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Pengaturan Admin
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [AdminSettingsController::class, 'index'])
            ->name('settings.index');

        Route::put(
            '/settings/password',
            [AdminSettingsController::class, 'updatePassword']
        )->name('settings.password');

        Route::put(
            '/settings/two-factor',
            [AdminSettingsController::class, 'toggleTwoFactor']
        )->name('settings.2fa');

        Route::get(
            '/money-currency',
            [MoneyCurrencyController::class, 'index']
        )->name('money-currency.index');

        Route::post(
            '/money-currency/convert',
            [MoneyCurrencyController::class, 'convert']
        )->name('money-currency.convert');

        /*
        |--------------------------------------------------------------------------
        | Kelola Data PDRB Daerah & Provinsi (Admin Full Access)
        |--------------------------------------------------------------------------
        */
        Route::get('/pdrb', [AdminPdrbController::class, 'index'])->name('pdrb.index');
        Route::post('/pdrb/init', [AdminPdrbController::class, 'init'])->name('pdrb.init');
        Route::post('/pdrb/init-provinsi', [AdminPdrbController::class, 'initProvinsi'])->name('pdrb.init-provinsi');
        Route::get('/pdrb/entry/{kabupaten_id}/{tahun}', [AdminPdrbController::class, 'entry'])->name('pdrb.entry');
        Route::get('/pdrb/provinsi-entry/{provinsi_id}/{tahun}', [AdminPdrbController::class, 'entryProvinsi'])->name('pdrb.provinsi-entry');
        Route::post('/pdrb/save-entry', [AdminPdrbController::class, 'saveEntry'])->name('pdrb.save-entry');
        Route::post('/pdrb/save-provinsi-entry', [AdminPdrbController::class, 'saveEntryProvinsi'])->name('pdrb.save-provinsi-entry');
        Route::delete('/pdrb/group/{kabupaten_id}/{tahun}', [AdminPdrbController::class, 'destroyGroup'])->name('pdrb.destroy-group');
        Route::delete('/pdrb/provinsi-group/{provinsi_id}/{tahun}', [AdminPdrbController::class, 'destroyGroupProvinsi'])->name('pdrb.destroy-provinsi-group');

        /*
        |--------------------------------------------------------------------------
        | Kelola Data PDB Nasional (Admin Full Access)
        |--------------------------------------------------------------------------
        */
        Route::get('/pdb-nasional', [AdminPdbNasionalController::class, 'index'])->name('pdb-nasional.index');
        Route::post('/pdb-nasional/init', [AdminPdbNasionalController::class, 'init'])->name('pdb-nasional.init');
        Route::get('/pdb-nasional/entry/{tahun}', [AdminPdbNasionalController::class, 'entry'])->name('pdb-nasional.entry');
        Route::post('/pdb-nasional/save-entry', [AdminPdbNasionalController::class, 'saveEntry'])->name('pdb-nasional.save-entry');
        Route::delete('/pdb-nasional/group/{tahun}', [AdminPdbNasionalController::class, 'destroyGroup'])->name('pdb-nasional.destroy-group');
    });