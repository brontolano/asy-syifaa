<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ErpAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login via API — returns token + redirect URL based on role.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;

        // Find account by username or phone
        $account = ErpAccount::where('username', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$account || !Hash::check($request->password, $account->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Username/HP atau password salah.',
            ], 401);
        }

        if (!$account->is_active) {
            return response()->json([
                'ok' => false,
                'message' => 'Akun tidak aktif. Hubungi admin.',
            ], 403);
        }

        // Check lockout
        if ($account->locked_until && $account->locked_until->isFuture()) {
            return response()->json([
                'ok' => false,
                'message' => 'Akun terkunci sementara. Coba lagi nanti.',
            ], 429);
        }

        // Update login info
        $account->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'failed_login_count' => 0,
        ]);

        // Create Sanctum token
        $token = $account->createToken('sso-login')->plainTextToken;

        // Determine redirect based on role
        $roles = $account->getRoleNames()->toArray();
        $redirect = $this->getRedirectUrl($roles);

        return response()->json([
            'ok' => true,
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => [
                'id' => $account->id,
                'username' => $account->username,
                'full_name' => $account->full_name,
                'phone' => $account->phone,
                'roles' => $roles,
            ],
            'redirect' => $redirect,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['ok' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    protected function getRedirectUrl(array $roles): string
    {
        // All roles redirect to ERP
        $erpUrl = config('app.url', 'https://erp.asy-syifaa.com');
        return $erpUrl;
    }
}
