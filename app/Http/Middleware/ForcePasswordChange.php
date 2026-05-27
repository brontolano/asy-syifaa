<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('erp')->user();

        if ($user && $user->must_change_password) {
            $changePasswordUrl = route('filament.erp.pages.change-password');

            if ($request->url() !== $changePasswordUrl) {
                return redirect($changePasswordUrl);
            }
        }

        // Redirect Pendaftar dari default dashboard ke PendaftarDashboard
        if ($user && $user->hasRole('Pendaftar') && $request->path() === '/') {
            return redirect('/pendaftar-dashboard');
        }

        return $next($request);
    }
}
