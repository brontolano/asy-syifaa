<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class SsoWaliController extends Controller
{
    public function redirect(Request $request)
    {
        $user = auth('erp')->user();

        if (!$user) {
            return redirect(Filament::getLoginUrl());
        }

        $isWali = $user->hasRole('wali_santri')
               || $user->hasRole('orang_tua')
               || $user->hasRole('wali');

        if (!$isWali) {
            return redirect(Filament::getUrl());
        }

        $token  = $user->createToken('pwa-sso')->plainTextToken;
        $pwaUrl = rtrim(config('app.pwa_url', 'https://app.asy-syifaa.com'), '/');
        $url    = $pwaUrl . '/login?sso_token=' . urlencode($token);

        // Logout ERP session — aman karena ini full HTTP request, bukan AJAX
        auth('erp')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($url);
    }
}
