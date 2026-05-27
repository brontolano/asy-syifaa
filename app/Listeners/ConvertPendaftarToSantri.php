<?php

namespace App\Listeners;

use App\Events\DaftarUlangPaid;
use App\Services\SpmbService;
use App\Services\WebhookNotificationService;

class ConvertPendaftarToSantri
{
    public function __construct(
        protected SpmbService $spmbService,
        protected WebhookNotificationService $webhook,
    ) {}

    public function handle(DaftarUlangPaid $event): void
    {
        $this->spmbService->convertToSantri($event->registration);

        $this->webhook->notifyPaymentConfirmed(
            $event->registration,
            $event->payment->invoice?->invoice_number ?? '',
            number_format($event->payment->amount, 0, ',', '.'),
        );
    }
}
