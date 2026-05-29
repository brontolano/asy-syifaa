<?php

namespace App\Http\Responses\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class WaliLoginResponse implements Responsable
{
    /**
     * Fallback response — primary redirect sudah ditangani di Login::authenticate()
     * via $this->redirect() (Livewire native redirect).
     *
     * Ini tetap dipakai jika ada path lain yang resolve LoginResponse,
     * misalnya future fitur MFA atau login via API.
     */
    public function toResponse($request): RedirectResponse|\Illuminate\Routing\Redirector
    {
        $user = auth('erp')->user();

        if ($user && $user->hasAnyRole(['wali_santri', 'orang_tua', 'wali', 'Wali Santri'])) {
            return redirect()->route('filament.erp.pages.wali-portal');
        }

        return redirect()->intended(Filament::getUrl());
    }
}
