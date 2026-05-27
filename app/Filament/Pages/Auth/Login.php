<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
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
            ->placeholder('Username (staff) atau Nomor HP (santri/ortu)')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->helperText('Staff: gunakan username | Santri/Ortu: gunakan nomor HP');
    }

    /**
     * Override: detect if input is phone or username, then find the right credential.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        $loginId = trim($data['login_id']);

        // If starts with 0 or + or is all digits → treat as phone number
        if (preg_match('/^[\d\+][\d\-\s]+$/', $loginId)) {
            // Normalize: remove spaces and dashes
            $phone = preg_replace('/[\s\-]/', '', $loginId);
            return [
                'phone' => $phone,
                'password' => $data['password'],
            ];
        }

        // Otherwise treat as username
        return [
            'username' => $loginId,
            'password' => $data['password'],
        ];
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
