<?php

namespace App\Services;

use App\Models\PpdbDocument;
use App\Models\PpdbRegistration;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookNotificationService
{
    protected ?string $webhookUrl;
    protected int $timeout;
    protected string $session;

    public function __construct()
    {
        $this->webhookUrl = config('spmb.webhook_url');
        $this->timeout = config('spmb.webhook_timeout', 10);
        $this->session = config('spmb.webhook_session', 'default');
    }

    public function send(string $event, array $payload): ?array
    {
        if (empty($this->webhookUrl)) {
            Log::warning("Webhook URL not configured, skipping event: {$event}");
            return null;
        }

        $payload['event'] = $event;
        $payload['session'] = $this->session;

        $log = WebhookLog::create([
            'event' => $event,
            'payload' => $payload,
            'sent_at' => now(),
        ]);

        try {
            $response = Http::timeout($this->timeout)->post($this->webhookUrl, $payload);

            $log->update([
                'http_status' => $response->status(),
                'response_body' => $response->json() ?? ['raw' => $response->body()],
            ]);

            return $response->json();
        } catch (\Throwable $e) {
            Log::error("Webhook failed for event {$event}: {$e->getMessage()}");

            $log->update([
                'http_status' => 0,
                'response_body' => ['error' => $e->getMessage()],
            ]);

            return null;
        }
    }

    public function notifyRegistered(PpdbRegistration $registration, string $plainPassword): ?array
    {
        return $this->send('spmb.registered', [
            'full_name' => $registration->student_name,
            'student_name' => $registration->student_name,
            'username' => $registration->account?->username ?? '',
            'phone' => $registration->parent_phone,
            'guardian_phone' => $registration->parent_phone,
            'role' => 'Pendaftar',
            'temp_password' => $plainPassword,
            'registration_number' => $registration->registration_number,
            'portal_url' => config('spmb.portal_url'),
        ]);
    }

    public function notifyDocumentRejected(PpdbDocument $document): ?array
    {
        $reg = $document->registration;

        return $this->send('spmb.document.rejected', [
            'full_name' => $reg->student_name,
            'phone' => $reg->parent_phone,
            'guardian_phone' => $reg->parent_phone,
            'registration_number' => $reg->registration_number,
            'document_type' => config("spmb.mandatory_documents.{$document->document_type}", $document->document_type),
            'rejection_reason' => $document->rejection_reason,
            'portal_url' => config('spmb.portal_url'),
        ]);
    }

    public function notifyAllDocumentsVerified(PpdbRegistration $registration): ?array
    {
        return $this->send('spmb.documents.complete', [
            'full_name' => $registration->student_name,
            'phone' => $registration->parent_phone,
            'guardian_phone' => $registration->parent_phone,
            'registration_number' => $registration->registration_number,
            'portal_url' => config('spmb.portal_url'),
        ]);
    }

    public function notifySelectionResult(PpdbRegistration $registration): ?array
    {
        return $this->send('spmb.selection.decided', [
            'full_name' => $registration->student_name,
            'phone' => $registration->parent_phone,
            'guardian_phone' => $registration->parent_phone,
            'registration_number' => $registration->registration_number,
            'status' => $registration->status,
            'portal_url' => config('spmb.portal_url'),
        ]);
    }

    public function notifyPaymentConfirmed(PpdbRegistration $registration, string $invoiceNumber, string $amount): ?array
    {
        return $this->send('spmb.payment.confirmed', [
            'full_name' => $registration->student_name,
            'phone' => $registration->parent_phone,
            'guardian_phone' => $registration->parent_phone,
            'registration_number' => $registration->registration_number,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'portal_url' => config('spmb.portal_url'),
        ]);
    }

    public function sendCredential(PpdbRegistration $registration, string $username, string $plainPassword): ?array
    {
        return $this->notifyRegistered($registration, $plainPassword);
    }

    public function sendBroadcast(string $phone, string $message): ?array
    {
        return $this->send('spmb.broadcast', [
            'phone' => $phone,
            'guardian_phone' => $phone,
            'message' => $message,
        ]);
    }

    public function sendGeneric(array $payload): ?array
    {
        $event = $payload['event'] ?? 'generic';
        return $this->send($event, $payload);
    }
}
