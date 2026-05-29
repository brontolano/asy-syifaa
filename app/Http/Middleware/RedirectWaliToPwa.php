<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWaliToPwa
{
    /**
     * Jika user yang login adalah wali/orang tua,
     * buat SSO token dan redirect ke PWA app.
     * Wali tidak boleh mengakses halaman ERP sama sekali.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('erp')->user();

        if ($user) {
            $isWali = $user->hasRole('wali_santri')
                   || $user->hasRole('orang_tua')
                   || $user->hasRole('wali');

            if ($isWali) {
                // Buat Sanctum token untuk SSO ke PWA
                $token = $user->createToken('pwa-sso')->plainTextToken;

                $pwaUrl      = rtrim(config('app.pwa_url', 'https://app.asy-syifaa.com'), '/');
                $redirectUrl = $pwaUrl . '?sso_token=' . urlencode($token);

                // Logout dari ERP session
                auth('erp')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect($redirectUrl);
            }
        }

        return $next($request);
    }
}
