<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TagihanDibuat extends Notification
{
    use Queueable;

    public function __construct(
        protected Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tagihan Baru',
            'body' => "Tagihan baru: {$this->invoice->invoice_number} - Rp " . number_format($this->invoice->total_amount, 0, ',', '.'),
            'icon' => 'heroicon-o-document-text',
            'color' => 'warning',
        ];
    }
}
