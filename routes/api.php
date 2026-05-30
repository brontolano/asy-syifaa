<?php

use App\Http\Controllers\Api\SpmbRegisterController;
use App\Http\Controllers\Api\SpmbSyncController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GalleryController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\PpdbPublicController;
use App\Http\Controllers\Api\V1\WaliController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('spmb')->group(function () {
    Route::post('/sync', [SpmbSyncController::class, 'sync']);
    Route::get('/{externalRefId}/status', [SpmbSyncController::class, 'status']);
});

// Phone change verification
Route::get('/v1/verify-phone', function (Request $request) {
    $token = $request->query('token');
    $userId = $request->query('user_id');

    if (!$token || !$userId) {
        return response()->json(['message' => 'Link tidak valid.'], 400);
    }

    $cached = cache()->get("phone_change_{$userId}");
    if (!$cached || $cached['token'] !== $token) {
        return response('<html><body style="font-family:sans-serif;text-align:center;padding:60px;"><h2 style="color:#dc2626;">Link tidak valid atau sudah kadaluarsa.</h2><p>Silakan ulangi proses perubahan nomor HP dari aplikasi.</p></body></html>', 400)
            ->header('Content-Type', 'text/html');
    }

    $account = \App\Models\ErpAccount::find($userId);
    if (!$account) {
        return response('<html><body style="font-family:sans-serif;text-align:center;padding:60px;"><h2 style="color:#dc2626;">Akun tidak ditemukan.</h2></body></html>', 404)
            ->header('Content-Type', 'text/html');
    }

    $account->update(['phone' => $cached['new_phone']]);
    cache()->forget("phone_change_{$userId}");

    return response('<html><body style="font-family:sans-serif;text-align:center;padding:60px;background:#f0fdf4;">
        <div style="max-width:400px;margin:auto;background:white;padding:40px;border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <div style="width:60px;height:60px;background:#10b981;border-radius:50%;margin:auto;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 style="color:#065f46;margin-bottom:8px;">Nomor HP Berhasil Diubah!</h2>
            <p style="color:#6b7280;">Nomor HP Anda telah diubah menjadi <strong>' . $cached['new_phone'] . '</strong>.</p>
            <p style="color:#6b7280;margin-top:12px;">Silakan login kembali menggunakan nomor baru.</p>
            <a href="/" style="display:inline-block;margin-top:20px;padding:10px 24px;background:#10b981;color:white;text-decoration:none;border-radius:8px;font-weight:bold;">Login Sekarang</a>
        </div>
    </body></html>')
        ->header('Content-Type', 'text/html');
});

Route::prefix('v1/spmb')->group(function () {
    Route::post('/register', [SpmbRegisterController::class, 'register']);
    Route::get('/{registrationNumber}/status', [SpmbRegisterController::class, 'status']);
    Route::get('/{registrationNumber}/documents', [SpmbRegisterController::class, 'documents']);
});

// ── Public API v1 ──────────────────────────────────────────────
Route::prefix('v1')->group(function () {
    // Health
    Route::get('/health', fn () => response()->json([
        'ok' => true,
        'service' => 'erp-pesantren-api',
        'time' => now()->toISOString(),
    ]));

    // CMS — public
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{slug}', [PostController::class, 'show']);
    Route::get('/galleries', [GalleryController::class, 'index']);
    Route::get('/galleries/{slug}', [GalleryController::class, 'show']);

    // PPDB — public
    Route::get('/ppdb/status/{registrationNumber}', [PpdbPublicController::class, 'status']);
    Route::get('/ppdb/selection-results', [PpdbPublicController::class, 'selectionResults']);

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Auth — protected
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
    });

    // ── Wali App (app.asy-syifaa.com) — protected ──────────────
    Route::prefix('wali')->middleware('auth:sanctum')->group(function () {
        Route::get('/santri', [WaliController::class, 'santri']);
        Route::prefix('santri/{studentId}')->group(function () {
            // Status
            Route::get('/status-harian',                       [WaliController::class, 'statusHarian']);

            // Keuangan
            Route::get('/tagihan',                             [WaliController::class, 'tagihan']);
            Route::post('/tagihan/{invoiceId}/bukti-bayar',    [WaliController::class, 'uploadBuktiBayar']);
            Route::get('/payment-methods',                     [WaliController::class, 'paymentMethods']);
            Route::get('/tabungan',                            [WaliController::class, 'tabungan']);
            Route::post('/tabungan/limit',                     [WaliController::class, 'setLimitTabungan']);
            Route::post('/tabungan/freeze',                    [WaliController::class, 'freezeTabungan']);
            Route::post('/tabungan/topup',                     [WaliController::class, 'topupTabungan']);
            Route::get('/transaksi',                           [WaliController::class, 'transaksi']);

            // Akademik & Hafalan
            Route::get('/hafalan',                             [WaliController::class, 'hafalan']);
            Route::get('/jadwal',                              [WaliController::class, 'jadwal']);
            Route::get('/absensi',                             [WaliController::class, 'absensi']);
            Route::get('/akademik',                            [WaliController::class, 'akademik']);
            Route::get('/prestasi',                            [WaliController::class, 'prestasi']);

            // Kegiatan
            Route::get('/izin',                                [WaliController::class, 'daftarIzin']);
            Route::post('/izin',                               [WaliController::class, 'ajukanIzin']);
            Route::get('/kesehatan',                           [WaliController::class, 'kesehatan']);
            Route::get('/kunjungan',                           [WaliController::class, 'daftarKunjungan']);
            Route::post('/kunjungan',                          [WaliController::class, 'ajukanKunjungan']);
            Route::get('/konseling',                           [WaliController::class, 'daftarKonseling']);
            Route::post('/konseling',                          [WaliController::class, 'ajukanKonseling']);
            Route::get('/presensi',                            [WaliController::class, 'presensi']);
        });
    });
});
