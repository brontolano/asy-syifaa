<?php

namespace App\Notifications;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SetoranTabunganDiterima extends Notification
{
    use Queueable;

    public function __construct(
        protected PaymentProof $proof,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Setoran Tabungan Dikonfirmasi',
            'body'  => 'Setoran Rp ' . number_format((float) $this->proof->nominal_transfer, 0, ',', '.')
                . ' telah ditambahkan ke saldo tabungan santri. Terima kasih!',
            'icon'  => 'heroicon-o-banknotes',
            'color' => 'success',
        ];
    }
}
