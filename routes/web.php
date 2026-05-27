<?php

use App\Http\Controllers\PublicVerificationController;
use Illuminate\Support\Facades\Route;

// Public verification page (accessible via QR code scan or manual search)
Route::get('/verifikasi', [PublicVerificationController::class, 'index'])->name('public.verifikasi');
Route::get('/verifikasi/{registrationNumber}', [PublicVerificationController::class, 'show'])->where('registrationNumber', '.*')->name('public.verifikasi.show');

// PDF Routes (authenticated via erp guard)
Route::middleware(['auth:erp'])->prefix('pdf')->name('pdf.')->group(function () {
    Route::get('/nota/{payment}', [\App\Http\Controllers\PdfController::class, 'notaPembayaran'])->name('nota');
    Route::get('/struk/{payment}', [\App\Http\Controllers\PdfController::class, 'notaThermal'])->name('struk');
    Route::get('/buku-spp/{student}/{year}', [\App\Http\Controllers\PdfController::class, 'bukuSpp'])->name('buku-spp');
    Route::get('/tagihan-massal', [\App\Http\Controllers\PdfController::class, 'tagihanMassal'])->name('tagihan-massal');
    Route::get('/buku-setoran', [\App\Http\Controllers\PdfController::class, 'bukuSetoran'])->name('buku-setoran');
    Route::get('/laporan-periode', [\App\Http\Controllers\PdfController::class, 'laporanPeriode'])->name('laporan-periode');
    Route::get('/matrix-syahriyyah', [\App\Http\Controllers\PdfController::class, 'matrixSyahriyyah'])->name('matrix-syahriyyah');
});

// Filament handles the root path via ErpPanelProvider
