<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranDiterima extends Notification
{
    use Queueable;

    public function __construct(
        protected Invoice $invoice,
        protected Payment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pembayaran Dikonfirmasi',
            'body' => 'Pembayaran Rp ' . number_format($this->payment->amount, 0, ',', '.') . ' untuk tagihan ' . $this->invoice->invoice_number . ' telah dikonfirmasi. Terima kasih!',
            'icon' => 'heroicon-o-check-badge',
            'color' => 'success',
        ];
    }
}
