<?php

namespace App\Notifications;

use App\Models\PpdbRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class StatusSeleksi extends Notification
{
    use Queueable;

    public function __construct(
        protected PpdbRegistration $registration,
        protected string $result,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = match ($this->result) {
            'lulus' => 'Lulus',
            'cadangan' => 'Cadangan',
            default => 'Tidak Lulus',
        };

        return [
            'title' => 'Hasil Seleksi',
            'body' => "Hasil seleksi Anda: {$statusLabel}. Silakan cek portal untuk informasi lebih lanjut.",
            'icon' => 'heroicon-o-academic-cap',
            'color' => $this->result === 'lulus' ? 'success' : ($this->result === 'cadangan' ? 'warning' : 'danger'),
        ];
    }
}
