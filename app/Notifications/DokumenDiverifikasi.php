<?php

namespace App\Notifications;

use App\Models\PpdbDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DokumenDiverifikasi extends Notification
{
    use Queueable;

    public function __construct(
        protected PpdbDocument $document,
        protected string $action,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeName = config("spmb.mandatory_documents.{$this->document->document_type}", $this->document->document_type);
        $status = $this->action === 'approved' ? 'disetujui' : 'ditolak';

        $body = "Dokumen {$typeName} telah {$status}.";
        if ($this->action === 'rejected' && $this->document->rejection_reason) {
            $body .= " Alasan: {$this->document->rejection_reason}";
        }

        return [
            'title' => 'Verifikasi Dokumen',
            'body' => $body,
            'icon' => $this->action === 'approved' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            'color' => $this->action === 'approved' ? 'success' : 'danger',
        ];
    }
}
