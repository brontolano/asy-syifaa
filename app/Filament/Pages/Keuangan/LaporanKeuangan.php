<?php

namespace App\Filament\Pages\Keuangan;

use App\Models\HijriBillingPeriod;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use Filament\Pages\Page;

class LaporanKeuangan extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'Laporan Keuangan';
    protected static ?string $title = 'Laporan & Rekap Keuangan';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.keuangan.laporan-keuangan';

    public string $reportType = 'dashboard';
    public ?string $filterPeriod = 'bulan_ini';
    public ?string $filterKelas = null;
    public ?string $filterJenjang = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $matrixYear = '1447';

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Mudir', 'Wakil Mudir', 'Bendahara', 'Kepala TU']);
    }

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updatedFilterPeriod(): void
    {
        [$this->dateFrom, $this->dateTo] = match ($this->filterPeriod) {
            'hari_ini' => [now()->toDateString(), now()->toDateString()],
            'bulan_ini' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'tahun_ini' => [now()->startOfYear()->toDateString(), now()->toDateString()],
            default => [$this->dateFrom, $this->dateTo],
        };
    }

    public function getDashboardKpiProperty(): array
    {
        $payments = Payment::whereBetween('payment_date', [$this->dateFrom, $this->dateTo])->get();

        $totalTransaksi = $payments->count();
        $totalPemasukan = $payments->sum('amount');
        $totalCash = $payments->where('payment_method', 'cash')->sum('amount');
        $totalTransfer = $payments->where('payment_method', 'transfer')->sum('amount');

        // Breakdown by channel
        $byChannel = $payments->groupBy('payment_channel')->map(fn ($g) => [
            'count' => $g->count(),
            'total' => $g->sum('amount'),
        ])->sortByDesc('total');

        // Top payment types
        $byType = $payments->groupBy(fn ($p) => $p->invoice?->invoice_type ?? 'lainnya')
            ->map(fn ($g) => ['count' => $g->count(), 'total' => $g->sum('amount')])
            ->sortByDesc('total');

        // Top 10 tunggakan
        $topTunggakan = Student::where('status', 'aktif')
            ->where('tunggakan_bulan', '>', 0)
            ->orderByDesc('tunggakan_bulan')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'nis' => $s->nis,
                'name' => $s->full_name,
                'kelas' => $s->kelas_detail ?? $s->kelas,
                'bulan' => $s->tunggakan_bulan,
                'nominal' => $s->total_tunggakan,
            ]);

        // Trend 12 bulan terakhir
        $trend = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthPayments = Payment::whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->get();
            $trend->push([
                'label' => $month->format('M Y'),
                'cash' => $monthPayments->where('payment_method', 'cash')->sum('amount'),
                'transfer' => $monthPayments->where('payment_method', 'transfer')->sum('amount'),
                'total' => $monthPayments->sum('amount'),
            ]);
        }

        return compact('totalTransaksi', 'totalPemasukan', 'totalCash', 'totalTransfer', 'byChannel', 'byType', 'topTunggakan', 'trend');
    }

    public function getRingkasanProperty(): array
    {
        $totalTagihan = Invoice::whereNotIn('status', ['cancelled', 'draft'])->sum('total_amount');
        $totalBayar = Payment::sum('amount');
        $totalTunggakan = $totalTagihan - $totalBayar;
        $totalSantriAktif = Student::where('status', 'aktif')->count();
        $santriLunas = Student::where('status', 'aktif')->where('tunggakan_bulan', 0)->count();
        $santriTunggakan = Student::where('status', 'aktif')->where('tunggakan_bulan', '>', 0)->count();
        $santriWaqof = Student::where('status', 'waqof')->count();

        return compact('totalTagihan', 'totalBayar', 'totalTunggakan', 'totalSantriAktif', 'santriLunas', 'santriTunggakan', 'santriWaqof');
    }

    public function getTunggakanPerKelasProperty(): array
    {
        return Student::where('status', 'aktif')
            ->where('tunggakan_bulan', '>', 0)
            ->selectRaw("COALESCE(kelas, '-') as kelas, COUNT(*) as jumlah, SUM(tunggakan_bulan) as total_bulan")
            ->groupBy('kelas')
            ->orderBy('kelas')
            ->get()
            ->map(function ($row) {
                $totalAmount = Invoice::whereHas('student', fn($q) => $q->where('kelas', $row->kelas))
                    ->whereIn('status', ['issued', 'partial', 'overdue'])
                    ->selectRaw('SUM(total_amount - paid_amount) as tunggakan')
                    ->value('tunggakan') ?? 0;

                return [
                    'kelas' => $row->kelas,
                    'jumlah_santri' => $row->jumlah,
                    'total_bulan' => $row->total_bulan,
                    'total_amount' => $totalAmount,
                ];
            })
            ->toArray();
    }

    public function getRekapPerBulanProperty(): array
    {
        return HijriBillingPeriod::orderBy('hijri_year')
            ->orderBy('hijri_month')
            ->get()
            ->map(function ($period) {
                $invoices = Invoice::where('hijri_billing_period_id', $period->id)->where('invoice_type', 'spp');
                $total = $invoices->sum('total_amount');
                $paid = $invoices->sum('paid_amount');
                $count = $invoices->count();
                $lunas = (clone $invoices)->where('status', 'paid')->count();

                return [
                    'label' => $period->label,
                    'total_tagihan' => $total,
                    'total_bayar' => $paid,
                    'tunggakan' => $total - $paid,
                    'jumlah_invoice' => $count,
                    'lunas' => $lunas,
                    'persen' => $total > 0 ? round(($paid / $total) * 100) : 0,
                ];
            })
            ->toArray();
    }

    public function getTransaksiTerakhirProperty(): array
    {
        $query = Payment::with(['invoice.student', 'receiver'])->latest();

        if ($this->dateFrom && $this->dateTo) {
            $query->whereBetween('payment_date', [$this->dateFrom, $this->dateTo]);
        }

        return $query->limit(100)->get()->map(fn ($p) => [
            'id' => $p->id,
            'date' => $p->payment_date?->format('d/m/Y'),
            'student' => $p->invoice?->student_name ?? '-',
            'nis' => $p->invoice?->student_id ?? '-',
            'invoice' => $p->invoice?->invoice_number ?? '-',
            'amount' => $p->amount,
            'method' => $p->payment_method,
            'channel' => $p->payment_channel,
            'reference' => $p->reference_number,
        ])->toArray();
    }

    public function getDaftarTunggakanProperty(): array
    {
        $query = Student::where('status', 'aktif')
            ->where('tunggakan_bulan', '>', 0)
            ->orderByDesc('tunggakan_bulan');

        if ($this->filterKelas) $query->where('kelas', $this->filterKelas);
        if ($this->filterJenjang) $query->where('jenjang', $this->filterJenjang);

        return $query->limit(200)->get()->map(function ($s) {
            $tunggakanAmount = Invoice::where('student_id_fk', $s->id)
                ->whereIn('status', ['issued', 'partial', 'overdue'])
                ->selectRaw('SUM(total_amount - paid_amount) as total')
                ->value('total') ?? 0;

            return [
                'id' => $s->id,
                'nis' => $s->nis,
                'name' => $s->full_name,
                'kelas' => $s->kelas_detail ?? $s->kelas,
                'jenjang' => $s->jenjang,
                'tunggakan_bulan' => $s->tunggakan_bulan,
                'tunggakan_amount' => $tunggakanAmount,
                'phone' => $s->phone,
                'wali' => $s->wali_nama_display,
            ];
        })->toArray();
    }

    public function getMatrixDataProperty(): array
    {
        $periods = HijriBillingPeriod::where('hijri_year', $this->matrixYear)->orderBy('hijri_month')->get();
        $query = Student::where('status', 'aktif')->orderBy('kelas')->orderBy('full_name');
        if ($this->filterKelas) $query->where('kelas', $this->filterKelas);
        $students = $query->limit(100)->get();

        $matrix = [];
        foreach ($students as $student) {
            $row = [
                'nis' => $student->nis,
                'name' => $student->full_name,
                'kelas' => $student->kelas,
                'months' => [],
            ];
            foreach ($periods as $period) {
                $invoice = Invoice::where('student_id_fk', $student->id)
                    ->where('hijri_billing_period_id', $period->id)
                    ->where('invoice_type', 'spp')
                    ->first();
                $row['months'][$period->id] = $invoice ? $invoice->status : 'none';
            }
            $matrix[] = $row;
        }

        return ['periods' => $periods, 'matrix' => $matrix];
    }
}
