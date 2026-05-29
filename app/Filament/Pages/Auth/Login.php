<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override: use login_id field that accepts both phone and username.
     */
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login_id')
            ->label('Username atau Nomor HP')
            ->placeholder('Username (staff) atau Nomor HP (ortu/wali)')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->helperText('Staff: gunakan username &nbsp;|&nbsp; Orang Tua/Wali: gunakan nomor HP');
    }

    /**
     * Override: detect if input is phone or username, then find the right credential.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $loginId = trim($data['login_id']);

        // If starts with 0 or + or is all digits → treat as phone number
        if (preg_match('/^[\d\+][\d\-\s]+$/', $loginId)) {
            $phone = preg_replace('/[\s\-]/', '', $loginId);
            return [
                'phone'    => $phone,
                'password' => $data['password'],
            ];
        }

        return [
            'username' => $loginId,
            'password' => $data['password'],
        ];
    }

    /**
     * Override: after successful login, redirect wali_santri / orang_tua to PWA app with SSO token.
     * All other roles proceed normally to ERP dashboard.
     */
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        $user = auth('erp')->user();

        if ($user) {
            $isWali = $user->hasRole('wali_santri')
                    || $user->hasRole('orang_tua')
                    || $user->hasRole('wali');

            if ($isWali) {
                // Buat token Sanctum khusus untuk SSO ke PWA
                $token = $user->createToken('pwa-sso')->plainTextToken;

                $pwaUrl      = rtrim(config('app.pwa_url', 'https://app.asy-syifaa.com'), '/');
                $redirectUrl = $pwaUrl . '?sso_token=' . urlencode($token);

                // Hapus sesi ERP — wali tidak perlu akses ERP
                auth('erp')->logout();
                session()->invalidate();
                session()->regenerateToken();

                $this->redirect($redirectUrl, navigate: false);
                return null;
            }
        }

        // Role lain: lanjut ke dashboard ERP seperti biasa
        return $response;
    }

    /**
     * Override: throw error on the login_id field.
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login_id' => 'Username/Nomor HP atau password salah.',
        ]);
    }
}
