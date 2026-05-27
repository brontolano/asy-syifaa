<?php

namespace App\Listeners;

use App\Events\SpmbRegistered;
use App\Services\WebhookNotificationService;

class SendRegistrationWebhook
{
    public function __construct(
        protected WebhookNotificationService $webhook,
    ) {}

    public function handle(SpmbRegistered $event): void
    {
        $this->webhook->notifyRegistered($event->registration, $event->plainPassword);
    }
}
