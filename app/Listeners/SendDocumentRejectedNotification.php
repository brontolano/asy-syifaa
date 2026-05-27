<?php

namespace App\Listeners;

use App\Events\DocumentVerified;
use App\Notifications\DokumenDiverifikasi;
use App\Services\WebhookNotificationService;

class SendDocumentRejectedNotification
{
    public function __construct(
        protected WebhookNotificationService $webhook,
    ) {}

    public function handle(DocumentVerified $event): void
    {
        $registration = $event->document->registration;
        $account = $registration->account;

        // Send in-app notification
        if ($account) {
            $account->notify(new DokumenDiverifikasi($event->document, $event->action));
        }

        // Send WhatsApp notification for rejected documents
        if ($event->action === 'rejected') {
            $this->webhook->notifyDocumentRejected($event->document);
        }
    }
}
