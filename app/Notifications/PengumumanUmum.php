<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengumumanUmum extends Notification
{
    use Queueable;

    public function __construct(
        protected string $judul,
        protected string $pesan,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->judul,
            'body' => $this->pesan,
            'icon' => 'heroicon-o-megaphone',
            'color' => 'info',
        ];
    }
}
