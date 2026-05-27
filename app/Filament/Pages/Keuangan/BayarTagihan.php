<?php

namespace App\Filament\Pages\Keuangan;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Student;
use App\Services\KeuanganService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\WithFileUploads;

class BayarTagihan extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';
    protected static ?string $navigationLabel = 'POS Pembayaran';
    protected static ?string $title = 'POS Pembayaran Santri';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.keuangan.bayar-tagihan';

    public ?string $student_id = null;
    public ?string $amount = null;
    public ?string $payment_channel = null;
    public ?string $reference_number = null;
    public ?string $notes = null;
    public ?string $specific_invoice_id = null;
    public $proof_image = null; // For file upload

    // Display state
    public ?array $studentInfo = null;
    public ?array $unpaidInvoices = null;
    public ?array $paymentResult = null;
    public ?int $lastPaymentId = null;

    public static function canAccess(): bool
    {
        $user = auth('erp')->user();
        return $user && $user->hasAnyRole(['Superadmin', 'Admin', 'Bendahara', 'Kepala TU', 'Staf TU']);
    }

    public function getPaymentMethodsProperty(): array
    {
        return PaymentMethod::active()->get()->map(fn ($m) => [
            'code' => $m->code,
            'name' => $m->name,
            'bank_name' => $m->bank_name,
            'account_number' => $m->account_number,
            'account_holder' => $m->account_holder,
            'is_active' => $m->is_active,
        ])->toArray();
    }

    public function getRecentPaymentsProperty(): array
    {
        return Payment::with(['invoice.student', 'receiver'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'date' => $p->payment_date?->format('d/m/Y H:i'),
                'student' => $p->invoice?->student_name ?? '-',
                'nis' => $p->invoice?->student_id ?? '-',
                'invoice' => $p->invoice?->invoice_number ?? '-',
                'amount' => $p->amount,
                'method' => ucfirst($p->payment_channel ?? $p->payment_method),
                'receiver' => $p->receiver?->full_name ?? '-',
            ])
            ->toArray();
    }

    public function searchStudent(): void
    {
        if (empty($this->student_id)) {
            Notification::make()->title('Ketik NIS atau nama santri')->warning()->send();
            return;
        }

        $student = Student::where('nis', $this->student_id)
            ->orWhere('full_name', 'ilike', '%' . $this->student_id . '%')
            ->first();

        if (!$student) {
            Notification::make()->title('Santri tidak ditemukan')->danger()->send();
            $this->studentInfo = null;
            $this->unpaidInvoices = null;
            return;
        }

        $this->studentInfo = [
            'id' => $student->id,
            'nis' => $student->nis,
            'name' => $student->full_name,
            'kelas' => $student->kelas_detail ?? $student->kelas,
            'jenjang' => $student->jenjang,
            'status' => $student->status,
            'spp' => $student->spp_amount,
            'tunggakan_bulan' => $student->tunggakan_bulan,
            'phone' => $student->phone,
            'wali' => $student->wali_nama_display,
        ];

        $this->unpaidInvoices = Invoice::where('student_id_fk', $student->id)
            ->whereIn('status', ['issued', 'partial', 'overdue'])
            ->orderByRaw("CASE WHEN status = 'overdue' THEN 0 ELSE 1 END")
            ->orderBy('due_date')
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'number' => $inv->invoice_number,
                'type' => $inv->invoice_type,
                'label' => $inv->hijri_label ?? $inv->invoice_type,
                'total' => $inv->total_amount,
                'paid' => $inv->paid_amount,
                'remaining' => $inv->remaining,
                'status' => $inv->status,
                'due_date' => $inv->due_date?->format('d/m/Y'),
            ])
            ->toArray();

        $this->paymentResult = null;
        $this->lastPaymentId = null;
    }

    public function quickFill(string $amount): void
    {
        $this->amount = $amount;
    }

    public function processPayment(): void
    {
        if (!$this->studentInfo || !$this->amount || !$this->payment_channel) {
            Notification::make()->title('Lengkapi data pembayaran (nominal & metode)')->danger()->send();
            return;
        }

        $student = Student::find($this->studentInfo['id']);
        if (!$student) return;

        // Handle proof image upload
        $proofPath = null;
        if ($this->proof_image) {
            $proofPath = $this->proof_image->store('payment-proofs', 'public');
        }

        $service = new KeuanganService();
        $result = $service->processPayment(
            student: $student,
            amount: floatval($this->amount),
            paymentMethod: str_contains($this->payment_channel, 'transfer') ? 'transfer' : 'cash',
            paymentChannel: $this->payment_channel,
            referenceNumber: $this->reference_number,
            receivedBy: auth('erp')->id(),
            notes: $this->notes,
            specificInvoiceId: $this->specific_invoice_id ? intval($this->specific_invoice_id) : null,
        );

        // Save proof image to all created payments
        if ($proofPath && !empty($result['payments'])) {
            foreach ($result['payments'] as $payment) {
                $payment->proof_image = $proofPath;
                $payment->saveQuietly();
            }
            $this->lastPaymentId = $result['payments'][0]->id;
        } elseif (!empty($result['payments'])) {
            $this->lastPaymentId = $result['payments'][0]->id;
        }

        $this->paymentResult = [
            'total_paid' => $result['total_paid'],
            'remaining' => $result['remaining'],
            'status' => $result['status'],
            'count' => count($result['payments']),
            'invoices_paid' => collect($result['payments'])->map(fn ($p) => [
                'id' => $p->id,
                'invoice' => $p->invoice->invoice_number,
                'amount' => $p->amount,
                'label' => $p->invoice->hijri_label ?? $p->invoice->invoice_type,
            ])->toArray(),
        ];

        // Refresh student info
        $this->searchStudent();

        $message = match ($result['status']) {
            'overpaid' => 'Pembayaran berhasil. Kelebihan: Rp ' . number_format($result['remaining'], 0, ',', '.'),
            'no_invoice' => 'Tidak ada tagihan yang bisa dibayar.',
            default => 'Pembayaran berhasil dicatat.',
        };

        Notification::make()->title($message)->success()->send();

        // Reset form
        $this->amount = null;
        $this->reference_number = null;
        $this->notes = null;
        $this->specific_invoice_id = null;
        $this->proof_image = null;
    }

    public function resetForm(): void
    {
        $this->studentInfo = null;
        $this->unpaidInvoices = null;
        $this->paymentResult = null;
        $this->lastPaymentId = null;
        $this->student_id = null;
        $this->amount = null;
        $this->payment_channel = null;
        $this->reference_number = null;
        $this->notes = null;
        $this->proof_image = null;
    }
}
