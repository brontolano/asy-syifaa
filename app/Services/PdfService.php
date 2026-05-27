<?php

namespace App\Services;

use App\Models\LetterHeader;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\HijriBillingPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

class PdfService
{
    public function getHeader(?int $headerId = null): ?LetterHeader
    {
        if ($headerId) return LetterHeader::find($headerId);
        return LetterHeader::getDefault();
    }

    /**
     * Nota Pembayaran — receipt per payment transaction
     */
    public function notaPembayaran(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->load(['invoice.student', 'receiver']);
        $header = $this->getHeader();

        return Pdf::loadView('pdf.nota-pembayaran', [
            'payment' => $payment,
            'header' => $header,
        ])->setPaper([0, 0, 226.77, 500], 'portrait'); // 80mm thermal
    }

    /**
     * Nota Pembayaran A4 — formal receipt
     */
    public function notaPembayaranA4(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $payment->load(['invoice.student', 'receiver']);
        $header = $this->getHeader();

        return Pdf::loadView('pdf.nota-pembayaran-a4', [
            'payment' => $payment,
            'header' => $header,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Buku SPP — yearly SPP card per student
     */
    public function bukuSpp(Student $student, string $hijriYear): \Barryvdh\DomPDF\PDF
    {
        $periods = HijriBillingPeriod::where('hijri_year', $hijriYear)->orderBy('hijri_month')->get();
        $invoices = Invoice::where('student_id_fk', $student->id)
            ->where('invoice_type', 'spp')
            ->whereIn('hijri_billing_period_id', $periods->pluck('id'))
            ->with('payments')
            ->get()
            ->keyBy('hijri_billing_period_id');

        $header = $this->getHeader();

        return Pdf::loadView('pdf.buku-spp', [
            'student' => $student,
            'periods' => $periods,
            'invoices' => $invoices,
            'hijriYear' => $hijriYear,
            'header' => $header,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Tagihan Massal — arrears list per class
     */
    public function tagihanMassal(?string $kelas = null, ?string $jenjang = null): \Barryvdh\DomPDF\PDF
    {
        $query = Student::where('status', 'aktif')
            ->where('tunggakan_bulan', '>', 0)
            ->orderByDesc('tunggakan_bulan');

        if ($kelas) $query->where('kelas', $kelas);
        if ($jenjang) $query->where('jenjang', $jenjang);

        $students = $query->get()->map(function ($s) {
            $tunggakan = $s->invoices()
                ->whereIn('status', ['issued', 'partial', 'overdue'])
                ->selectRaw('SUM(total_amount - paid_amount) as total')
                ->value('total') ?? 0;
            $s->tunggakan_nominal = $tunggakan;
            return $s;
        });

        $header = $this->getHeader();

        return Pdf::loadView('pdf.tagihan-massal', [
            'students' => $students,
            'kelas' => $kelas,
            'jenjang' => $jenjang,
            'header' => $header,
            'tanggal' => now()->format('d/m/Y'),
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Buku Setoran — daily/monthly payment log
     */
    public function bukuSetoran(string $dateFrom, string $dateTo): \Barryvdh\DomPDF\PDF
    {
        $payments = Payment::with(['invoice.student', 'receiver'])
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderBy('payment_date')
            ->orderBy('created_at')
            ->get();

        $totalCash = $payments->where('payment_method', 'cash')->sum('amount');
        $totalTransfer = $payments->where('payment_method', 'transfer')->sum('amount');

        $header = $this->getHeader();

        return Pdf::loadView('pdf.buku-setoran', [
            'payments' => $payments,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalCash' => $totalCash,
            'totalTransfer' => $totalTransfer,
            'header' => $header,
        ])->setPaper('a4', 'landscape');
    }

    /**
     * Laporan Periode — comprehensive period report
     */
    public function laporanPeriode(string $dateFrom, string $dateTo): \Barryvdh\DomPDF\PDF
    {
        $payments = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->get();

        $totalTransaksi = $payments->count();
        $totalPemasukan = $payments->sum('amount');
        $totalCash = $payments->where('payment_method', 'cash')->sum('amount');
        $totalTransfer = $payments->where('payment_method', 'transfer')->sum('amount');

        // Breakdown by channel
        $byChannel = $payments->groupBy('payment_channel')->map(fn ($g) => [
            'count' => $g->count(),
            'total' => $g->sum('amount'),
        ]);

        $header = $this->getHeader();

        return Pdf::loadView('pdf.laporan-periode', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalTransaksi' => $totalTransaksi,
            'totalPemasukan' => $totalPemasukan,
            'totalCash' => $totalCash,
            'totalTransfer' => $totalTransfer,
            'byChannel' => $byChannel,
            'payments' => $payments,
            'header' => $header,
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Matrix Syahriyyah — student × 12 months matrix
     */
    public function matrixSyahriyyah(string $hijriYear, ?string $kelas = null): \Barryvdh\DomPDF\PDF
    {
        $periods = HijriBillingPeriod::where('hijri_year', $hijriYear)->orderBy('hijri_month')->get();
        $query = Student::where('status', 'aktif')->orderBy('kelas')->orderBy('full_name');
        if ($kelas) $query->where('kelas', $kelas);
        $students = $query->get();

        $matrix = [];
        foreach ($students as $student) {
            $row = ['student' => $student, 'months' => []];
            foreach ($periods as $period) {
                $invoice = Invoice::where('student_id_fk', $student->id)
                    ->where('hijri_billing_period_id', $period->id)
                    ->where('invoice_type', 'spp')
                    ->first();
                $row['months'][$period->id] = $invoice ? $invoice->status : 'none';
            }
            $matrix[] = $row;
        }

        $header = $this->getHeader();

        return Pdf::loadView('pdf.matrix-syahriyyah', [
            'matrix' => $matrix,
            'periods' => $periods,
            'hijriYear' => $hijriYear,
            'kelas' => $kelas,
            'header' => $header,
        ])->setPaper('a4', 'landscape');
    }
}
