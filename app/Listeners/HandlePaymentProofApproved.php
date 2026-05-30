<?php

namespace App\Listeners;

use App\Events\DaftarUlangPaid;
use App\Events\PaymentProofApproved;
use App\Models\Payment;
use App\Notifications\PembayaranDiterima;
use App\Services\WebhookNotificationService;
use Illuminate\Support\Facades\DB;

class HandlePaymentProofApproved
{
    public function __construct(
        protected WebhookNotificationService $webhook,
    ) {}

    public function handle(PaymentProofApproved $event): void
    {
        $proof = $event->paymentProof;

        // Setoran tabungan (topup) — kredit saldo santri, tidak ada invoice.
        if ($proof->type === 'topup') {
            $this->creditSavings($proof);
            return;
        }

        $invoice = $proof->invoice;

        // Create payment record
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_amount - $invoice->paid_amount,
            'payment_date' => now(),
            'payment_method' => 'transfer',
            'reference_number' => 'PROOF-' . $proof->id,
            'notes' => 'Dari bukti transfer #' . $proof->id,
        ]);

        // Invoice recalculate is handled by Payment model observer

        // Send in-app notification
        $uploader = $proof->uploader;
        if ($uploader) {
            $uploader->notify(new PembayaranDiterima($invoice, $payment));
        }

        // Send WhatsApp thank you
        $registration = $uploader?->registration;
        if ($registration) {
            $this->webhook->notifyPaymentConfirmed(
                $registration,
                $invoice->invoice_number,
                number_format($payment->amount, 0, ',', '.'),
            );

            // If daftar ulang invoice is fully paid, dispatch DaftarUlangPaid
            $invoice->refresh();
            if ($invoice->status === 'paid') {
                event(new DaftarUlangPaid($registration, $payment));
            }
        }
    }

    /**
     * Kredit saldo tabungan santri dari bukti setoran (topup) yang disetujui.
     * Operasi atomik dengan row lock agar saldo akurat saat verifikasi paralel.
     */
    protected function creditSavings(\App\Models\PaymentProof $proof): void
    {
        $nominal = (float) $proof->nominal_transfer;
        if ($proof->student_id === null || $nominal <= 0) {
            return;
        }

        DB::transaction(function () use ($proof, $nominal) {
            // Pastikan baris tabungan ada, lalu kunci untuk update saldo.
            DB::table('student_savings')->updateOrInsert(
                ['student_id' => $proof->student_id],
                ['updated_at' => now()]
            );

            $savings = DB::table('student_savings')
                ->where('student_id', $proof->student_id)
                ->lockForUpdate()
                ->first();

            $saldoSesudah = (float) $savings->saldo + $nominal;

            DB::table('student_savings')
                ->where('student_id', $proof->student_id)
                ->update([
                    'saldo'               => $saldoSesudah,
                    'last_transaction_at' => now(),
                    'updated_at'          => now(),
                ]);

            DB::table('savings_transactions')->insert([
                'student_id'    => $proof->student_id,
                'jenis'         => 'kredit',
                'kategori'      => 'setor_wali',
                'nominal'       => $nominal,
                'saldo_sesudah' => $saldoSesudah,
                'keterangan'    => 'Setoran wali (bukti #' . $proof->id . ')',
                'dicatat_oleh'  => $proof->reviewed_by,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        });

        // Notifikasi in-app ke wali yang menyetor.
        $uploader = $proof->uploader;
        if ($uploader) {
            $uploader->notify(new \App\Notifications\SetoranTabunganDiterima($proof));
        }
    }
}
