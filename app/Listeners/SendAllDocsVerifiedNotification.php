<?php

namespace App\Listeners;

use App\Events\AllDocumentsVerified;
use App\Services\WebhookNotificationService;

class SendAllDocsVerifiedNotification
{
    public function __construct(
        protected WebhookNotificationService $webhook,
    ) {}

    public function handle(AllDocumentsVerified $event): void
    {
        $this->webhook->notifyAllDocumentsVerified($event->registration);
    }
}
