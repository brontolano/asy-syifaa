<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Student;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function __construct(private PdfService $pdf) {}

    public function notaPembayaran(Payment $payment)
    {
        return $this->pdf->notaPembayaranA4($payment)->stream("nota-{$payment->id}.pdf");
    }

    public function notaThermal(Payment $payment)
    {
        return $this->pdf->notaPembayaran($payment)->stream("struk-{$payment->id}.pdf");
    }

    public function bukuSpp(Student $student, string $year)
    {
        return $this->pdf->bukuSpp($student, $year)->stream("buku-spp-{$student->nis}-{$year}.pdf");
    }

    public function tagihanMassal(Request $request)
    {
        return $this->pdf->tagihanMassal(
            $request->get('kelas'),
            $request->get('jenjang'),
        )->stream('tagihan-massal.pdf');
    }

    public function bukuSetoran(Request $request)
    {
        return $this->pdf->bukuSetoran(
            $request->get('from', now()->startOfMonth()->toDateString()),
            $request->get('to', now()->toDateString()),
        )->stream('buku-setoran.pdf');
    }

    public function laporanPeriode(Request $request)
    {
        return $this->pdf->laporanPeriode(
            $request->get('from', now()->startOfMonth()->toDateString()),
            $request->get('to', now()->toDateString()),
        )->stream('laporan-periode.pdf');
    }

    public function matrixSyahriyyah(Request $request)
    {
        return $this->pdf->matrixSyahriyyah(
            $request->get('year', '1447'),
            $request->get('kelas'),
        )->stream('matrix-syahriyyah.pdf');
    }
}
