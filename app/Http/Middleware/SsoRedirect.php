<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SsoRedirect
{
    /**
     * Redirect ERP login page to the unified website login.
     * Only active if WEBSITE_LOGIN_URL is configured.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $websiteLoginUrl = config('services.sso.website_login_url');

        if ($websiteLoginUrl && $request->is('login') && !$request->isMethod('POST') && !auth('erp')->check()) {
            return redirect($websiteLoginUrl . '?redirect=erp');
        }

        return $next($request);
    }
}
