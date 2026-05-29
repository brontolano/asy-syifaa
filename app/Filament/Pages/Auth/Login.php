<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * Override: gunakan field login_id yang menerima username atau nomor HP.
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
     * Override: deteksi apakah input adalah nomor HP atau username.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $loginId = trim($data['login_id']);

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
     * Override: tampilkan error di field login_id.
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login_id' => 'Username/Nomor HP atau password salah.',
        ]);
    }

    /**
     * Override authenticate: setelah login berhasil, redirect wali ke WaliPortal.
     * Staff/admin lanjut ke panel ERP seperti biasa.
     */
    public function authenticate(): ?LoginResponse
    {
        $result = parent::authenticate();

        // Null = MFA challenge atau gagal, kembalikan saja
        if ($result === null) {
            return null;
        }

        $user = auth('erp')->user();

        if ($user && $user->hasAnyRole(['wali_santri', 'orang_tua', 'wali', 'Wali Santri'])) {
            // Redirect ke WaliPortal menggunakan Livewire's $this->redirect()
            $this->redirect(route('filament.erp.pages.wali-portal'));
            return null;
        }

        return $result;
    }
}
