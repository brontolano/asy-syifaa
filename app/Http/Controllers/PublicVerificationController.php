<?php

namespace App\Http\Controllers;

use App\Models\PpdbRegistration;
use Illuminate\Http\Request;

class PublicVerificationController extends Controller
{
    /**
     * Halaman publik untuk cek status pendaftaran / verifikasi identitas.
     * Bisa diakses via QR code scan atau pencarian manual.
     */
    public function index(Request $request)
    {
        $query = $request->query('q');
        $registration = null;

        if ($query) {
            $registration = PpdbRegistration::with(['documents', 'account'])
                ->where('registration_number', $query)
                ->first();
        }

        return view('public.verifikasi', compact('registration', 'query'));
    }

    /**
     * Endpoint langsung via QR code: /verifikasi/{registrationNumber}
     */
    public function show(string $registrationNumber)
    {
        $registration = PpdbRegistration::with(['documents', 'account'])
            ->where('registration_number', $registrationNumber)
            ->first();

        $query = $registrationNumber;

        return view('public.verifikasi', compact('registration', 'query'));
    }
}
