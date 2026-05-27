<?php

use App\Http\Controllers\Api\SpmbRegisterController;
use App\Http\Controllers\Api\SpmbSyncController;
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
