<?php

namespace App\Listeners;

use App\Events\DaftarUlangPaid;
use App\Events\PaymentProofApproved;
use App\Models\Payment;
use App\Notifications\PembayaranDiterima;
use App\Services\WebhookNotificationService;

class HandlePaymentProofApproved
{
    public function __construct(
        protected WebhookNotificationService $webhook,
    ) {}

    public function handle(PaymentProofApproved $event): void
    {
        $proof = $event->paymentProof;
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
}
