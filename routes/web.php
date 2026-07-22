<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityFileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('drive.connected')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/riwayat', [HistoryController::class, 'index'])->name('history');

    Route::get('/kegiatan/tambah', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('/kegiatan', [ActivityController::class, 'store'])->name('activities.store');
    Route::get('/kegiatan/{activity}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
    Route::put('/kegiatan/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('/kegiatan/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    Route::post('/kegiatan/{activity}/upload/dasar', [ActivityFileController::class, 'uploadBasic'])
        ->name('activities.upload.basic');
    Route::post('/kegiatan/{activity}/upload/bukti', [ActivityFileController::class, 'uploadEvidence'])
        ->name('activities.upload.evidence');
});

Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');
Route::put('/pengaturan/google-drive', [SettingsController::class, 'updateGoogleDrive'])
    ->name('settings.google-drive.update');
Route::post('/pengaturan/google-drive/uji', [SettingsController::class, 'testGoogleDrive'])
    ->name('settings.google-drive.test');

Route::post('/pengaturan/kategori', [SettingsController::class, 'storeCategory'])
    ->name('settings.categories.store');
Route::put('/pengaturan/kategori/{category}', [SettingsController::class, 'updateCategory'])
    ->name('settings.categories.update');
Route::patch('/pengaturan/kategori/{category}/toggle', [SettingsController::class, 'toggleCategory'])
    ->name('settings.categories.toggle');
Route::delete('/pengaturan/kategori/{category}', [SettingsController::class, 'destroyCategory'])
    ->name('settings.categories.destroy');
