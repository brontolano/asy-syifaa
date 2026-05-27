<?php

namespace App\Listeners;

use App\Events\SelectionDecided;
use App\Notifications\StatusSeleksi;
use App\Services\SpmbService;
use App\Services\WebhookNotificationService;

class HandleSelectionDecision
{
    public function __construct(
        protected WebhookNotificationService $webhook,
        protected SpmbService $spmbService,
    ) {}

    public function handle(SelectionDecided $event): void
    {
        $registration = $event->registration;
        $account = $registration->account;

        // Generate invoice daftar ulang if lulus
        if ($event->result === 'lulus') {
            $this->spmbService->generateDaftarUlangInvoice($registration);
        }

        // Send in-app notification
        if ($account) {
            $account->notify(new StatusSeleksi($registration, $event->result));
        }

        // Send WhatsApp notification
        $this->webhook->notifySelectionResult($registration);
    }
}
