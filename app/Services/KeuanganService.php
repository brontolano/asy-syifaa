<?php

namespace App\Services;

use App\Models\BillingType;
use App\Models\HijriBillingPeriod;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Student;
use App\Models\WaqofLog;
use Illuminate\Support\Facades\DB;

class KeuanganService
{
    /**
     * Process a payment — automatically pays oldest tunggakan first.
     *
     * @return array{payments: Payment[], remaining: float, status: string}
     */
    public function processPayment(
        Student $student,
        float $amount,
        string $paymentMethod,
        string $paymentChannel,
        ?string $referenceNumber = null,
        ?int $receivedBy = null,
        ?string $notes = null,
        ?int $specificInvoiceId = null,
    ): array {
        return DB::transaction(function () use ($student, $amount, $paymentMethod, $paymentChannel, $referenceNumber, $receivedBy, $notes, $specificInvoiceId) {
            $payments = [];
            $remaining = $amount;
            $status = 'paid'; // Will be adjusted

            // If paying specific invoice
            if ($specificInvoiceId) {
                $invoice = Invoice::where('id', $specificInvoiceId)
                    ->where('student_id_fk', $student->id)
                    ->whereIn('status', ['issued', 'partial', 'overdue'])
                    ->first();

                if ($invoice) {
                    $result = $this->applyPaymentToInvoice($invoice, $remaining, $paymentMethod, $paymentChannel, $referenceNumber, $receivedBy, $notes, $student->id);
                    $payments[] = $result['payment'];
                    $remaining = $result['remaining'];
                }
            }

            // Pay oldest tunggakan first (priority: overdue > issued by date)
            if ($remaining > 0) {
                $unpaidInvoices = Invoice::where('student_id_fk', $student->id)
                    ->whereIn('status', ['issued', 'partial', 'overdue'])
                    ->when($specificInvoiceId, fn($q) => $q->where('id', '!=', $specificInvoiceId))
                    ->orderByRaw("CASE WHEN status = 'overdue' THEN 0 ELSE 1 END")
                    ->orderBy('due_date')
                    ->orderBy('created_at')
                    ->get();

                foreach ($unpaidInvoices as $invoice) {
                    if ($remaining <= 0) break;

                    $result = $this->applyPaymentToInvoice($invoice, $remaining, $paymentMethod, $paymentChannel, $referenceNumber, $receivedBy, $notes, $student->id);
                    $payments[] = $result['payment'];
                    $remaining = $result['remaining'];
                }
            }

            // Determine final status
            if ($remaining > 0) {
                $status = 'overpaid'; // Lebih bayar
            } elseif (count($payments) === 0) {
                $status = 'no_invoice'; // Tidak ada tagihan
            }

            // Recalculate tunggakan
            $student->recalculateTunggakan();

            return [
                'payments' => $payments,
                'remaining' => $remaining,
                'status' => $status,
                'total_paid' => $amount - $remaining,
            ];
        });
    }

    private function applyPaymentToInvoice(
        Invoice $invoice,
        float $availableAmount,
        string $paymentMethod,
        string $paymentChannel,
        ?string $referenceNumber,
        ?int $receivedBy,
        ?string $notes,
        int $studentId,
    ): array {
        $invoiceRemaining = $invoice->total_amount - ($invoice->paid_amount ?? 0);
        $payAmount = min($availableAmount, $invoiceRemaining);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'student_id' => $studentId,
            'payment_date' => now(),
            'amount' => $payAmount,
            'payment_method' => $paymentMethod,
            'payment_channel' => $paymentChannel,
            'reference_number' => $referenceNumber,
            'received_by' => $receivedBy,
            'notes' => $notes,
            'verified_at' => now(),
            'verified_by' => $receivedBy,
        ]);

        // recalculate is triggered by Payment::saved observer

        return [
            'payment' => $payment,
            'remaining' => $availableAmount - $payAmount,
        ];
    }

    /**
     * Generate monthly SPP invoices for all active students.
     */
    public function generateMonthlyInvoices(?HijriBillingPeriod $period = null): int
    {
        $period = $period ?? $this->getCurrentHijriPeriod();
        if (!$period) return 0;

        $sppType = BillingType::where('code', 'SPP')->first();
        if (!$sppType) return 0;

        $students = Student::where('status', 'aktif')->get();
        $created = 0;

        foreach ($students as $student) {
            $exists = Invoice::where('student_id_fk', $student->id)
                ->where('hijri_billing_period_id', $period->id)
                ->where('invoice_type', 'spp')
                ->exists();

            if ($exists) continue;

            $invoice = Invoice::create([
                'invoice_number' => sprintf('SPP-%s-%05d', $period->hijri_year . $period->hijri_month, $created + 1),
                'invoice_type' => 'spp',
                'hijri_label' => $period->label,
                'student_name' => $student->full_name,
                'student_id' => $student->nis,
                'student_id_fk' => $student->id,
                'hijri_billing_period_id' => $period->id,
                'total_amount' => $student->spp_amount,
                'paid_amount' => 0,
                'status' => 'issued',
                'due_date' => $period->due_date,
                'issued_at' => now(),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'billing_type_id' => $sppType->id,
                'description' => 'Syahriyyah ' . $period->label,
                'amount' => $student->spp_amount,
            ]);

            $created++;
        }

        // Update tunggakan count and check waqof threshold
        $this->updateAllTunggakan();

        return $created;
    }

    /**
     * Generate specific type of invoice for a student.
     */
    public function generateInvoice(
        Student $student,
        string $billingTypeCode,
        ?float $customAmount = null,
        ?string $description = null,
        ?\Carbon\Carbon $dueDate = null,
    ): Invoice {
        $billingType = BillingType::where('code', $billingTypeCode)->firstOrFail();
        $amount = $customAmount ?? $billingType->amount_default;

        $invoice = Invoice::create([
            'invoice_type' => strtolower($billingTypeCode),
            'student_name' => $student->full_name,
            'student_id' => $student->nis,
            'student_id_fk' => $student->id,
            'total_amount' => $amount,
            'paid_amount' => 0,
            'status' => 'issued',
            'due_date' => $dueDate ?? now()->addDays(30),
            'issued_at' => now(),
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'billing_type_id' => $billingType->id,
            'description' => $description ?? $billingType->name,
            'amount' => $amount,
        ]);

        return $invoice;
    }

    /**
     * Check and auto-waqof students with 3+ months tunggakan.
     */
    public function checkAndApplyWaqof(int $threshold = 3): array
    {
        $students = Student::where('status', 'aktif')
            ->where('tunggakan_bulan', '>=', $threshold)
            ->get();

        $waqofed = [];
        foreach ($students as $student) {
            $student->status = 'waqof';
            $student->waqof_at = now();
            $student->waqof_reason = "Tunggakan {$student->tunggakan_bulan} bulan (melebihi batas {$threshold} bulan)";
            $student->save();

            WaqofLog::create([
                'student_id' => $student->id,
                'action' => 'waqof',
                'reason' => $student->waqof_reason,
                'tunggakan_bulan' => $student->tunggakan_bulan,
                'tunggakan_amount' => $student->total_tunggakan,
            ]);

            $waqofed[] = $student;
        }

        return $waqofed;
    }

    /**
     * Reactivate waqof student after payment.
     */
    public function reactivateStudent(Student $student, ?int $actionedBy = null): void
    {
        $student->status = 'aktif';
        $student->waqof_at = null;
        $student->waqof_reason = null;
        $student->save();

        WaqofLog::create([
            'student_id' => $student->id,
            'action' => 'reactivate',
            'reason' => 'Pembayaran tunggakan telah diselesaikan',
            'tunggakan_bulan' => $student->tunggakan_bulan,
            'tunggakan_amount' => $student->total_tunggakan,
            'actioned_by' => $actionedBy,
        ]);
    }

    public function updateAllTunggakan(): void
    {
        Student::where('status', 'aktif')->each(function ($student) {
            $student->recalculateTunggakan();
        });
    }

    public function getCurrentHijriPeriod(): ?HijriBillingPeriod
    {
        return HijriBillingPeriod::where('gregorian_start', '<=', now())
            ->where('gregorian_end', '>=', now())
            ->first();
    }

    /**
     * Mark overdue invoices.
     */
    public function markOverdueInvoices(): int
    {
        return Invoice::whereIn('status', ['issued', 'partial'])
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }
}
