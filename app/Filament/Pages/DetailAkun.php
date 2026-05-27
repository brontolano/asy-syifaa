<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DetailAkun extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Detail Akun';
    protected static ?string $title = 'Detail Akun';
    protected static ?string $slug = 'detail-akun';
    protected static ?int $navigationSort = 99;

    public static function getNavigationGroup(): ?string
    {
        $user = auth('erp')->user();
        if ($user && $user->hasAnyRole(['Pendaftar', 'Santri', 'Wali Santri'])) {
            return 'SPMB';
        }
        return 'Pengaturan';
    }

    protected string $view = 'filament.pages.detail-akun';

    // Password form
    public ?string $current_password = null;
    public ?string $new_password = null;
    public ?string $new_password_confirmation = null;

    // Phone form
    public ?string $new_phone = null;
    public bool $otpSent = false;
    public ?string $otpToken = null;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user !== null;
    }

    public function getAccount()
    {
        return auth('erp')->user();
    }

    public function changePassword(): void
    {
        $user = $this->getAccount();

        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        if (strlen($this->new_password) < 8) {
            throw ValidationException::withMessages([
                'new_password' => 'Password baru minimal 8 karakter.',
            ]);
        }

        if ($this->new_password !== $this->new_password_confirmation) {
            throw ValidationException::withMessages([
                'new_password_confirmation' => 'Konfirmasi password tidak cocok.',
            ]);
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->current_password = null;
        $this->new_password = null;
        $this->new_password_confirmation = null;

        Notification::make()
            ->title('Password berhasil diubah')
            ->success()
            ->send();
    }

    public function sendPhoneOtp(): void
    {
        if (empty($this->new_phone) || strlen($this->new_phone) < 10) {
            throw ValidationException::withMessages([
                'new_phone' => 'Masukkan nomor HP yang valid.',
            ]);
        }

        $user = $this->getAccount();

        if ($this->new_phone === $user->phone) {
            throw ValidationException::withMessages([
                'new_phone' => 'Nomor HP baru sama dengan yang sekarang.',
            ]);
        }

        // Check if phone already used by another account
        $exists = \App\Models\ErpAccount::where('phone', $this->new_phone)
            ->where('id', '!=', $user->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'new_phone' => 'Nomor HP ini sudah digunakan akun lain.',
            ]);
        }

        // Generate OTP token
        $token = Str::random(32);
        $this->otpToken = $token;
        $this->otpSent = true;

        // Store token in cache with 15 min expiry
        cache()->put("phone_change_{$user->id}", [
            'token' => $token,
            'new_phone' => $this->new_phone,
        ], now()->addMinutes(15));

        // Send verification link via webhook to WhatsApp
        try {
            $verifyUrl = url("/api/v1/verify-phone?token={$token}&user_id={$user->id}");

            app(\App\Services\WebhookNotificationService::class)->sendGeneric([
                'event' => 'phone.verify',
                'phone' => $this->new_phone,
                'full_name' => $user->full_name,
                'message' => "Anda meminta perubahan nomor HP di ERP Pesantren Asy-Syifaa.\n\nKlik link berikut untuk verifikasi:\n{$verifyUrl}\n\nLink berlaku 15 menit.\n\nAbaikan jika Anda tidak merasa melakukan perubahan.",
                'session' => 'default',
            ]);
        } catch (\Throwable $e) {
            // Log but don't fail — admin can verify manually
            \Illuminate\Support\Facades\Log::warning('Failed to send phone change WA: ' . $e->getMessage());
        }

        Notification::make()
            ->title('Link verifikasi telah dikirim')
            ->body("Cek WhatsApp di nomor {$this->new_phone} untuk verifikasi. Link berlaku 15 menit.")
            ->success()
            ->send();
    }

    public function cancelPhoneChange(): void
    {
        $user = $this->getAccount();
        cache()->forget("phone_change_{$user->id}");
        $this->otpSent = false;
        $this->otpToken = null;
        $this->new_phone = null;

        Notification::make()
            ->title('Perubahan nomor HP dibatalkan')
            ->warning()
            ->send();
    }
}
