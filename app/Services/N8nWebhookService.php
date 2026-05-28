<?php

namespace App\Services;

use App\Models\BroadcastJob;
use App\Models\ErpAccount;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nWebhookService
{
    protected ?string $baseUrl;
    protected ?string $secret;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.n8n.base_url', config('spmb.webhook_url'));
        $this->secret = config('services.n8n.webhook_secret');
        $this->timeout = 15;
    }

    /**
     * Send a raw payload to an n8n webhook path.
     */
    public function send(string $webhookPath, array $payload): bool
    {
        $url = $this->baseUrl;
        if (!$url) {
            Log::warning("N8n webhook URL not configured, skipping: {$webhookPath}");
            return false;
        }

        // If baseUrl is a full webhook URL (legacy), use it directly
        // Otherwise construct from base + path
        if (!str_contains($url, '/webhook')) {
            $url = rtrim($url, '/') . $webhookPath;
        }

        try {
            $headers = [];
            if ($this->secret) {
                $headers['X-Webhook-Secret'] = $this->secret;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->post($url, $payload);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("N8n webhook failed for {$webhookPath}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send a notification using a template and log it.
     */
    public function sendNotification(
        NotificationTemplate $template,
        string $recipient,
        array $vars,
        ?string $notifiableType = null,
        ?int $notifiableId = null,
    ): NotificationLog {
        $body = $template->renderBody($vars);

        $log = NotificationLog::create([
            'template_id' => $template->id,
            'channel' => $template->channel,
            'recipient' => $recipient,
            'subject' => $template->subject,
            'body' => $body,
            'payload' => $vars,
            'status' => 'pending',
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
        ]);

        $payload = array_merge($vars, [
            'phone' => $recipient,
            'guardian_phone' => $recipient,
            'message' => $body,
            'channel' => $template->channel,
            'template_slug' => $template->slug,
        ]);

        $success = $this->send('/webhook/notification', $payload);

        $log->update([
            'status' => $success ? 'sent' : 'failed',
            'sent_at' => $success ? now() : null,
            'error' => $success ? null : 'Webhook call failed',
        ]);

        return $log;
    }

    /**
     * Log a notification that was sent via the legacy WebhookNotificationService.
     */
    public function logNotification(
        string $channel,
        string $recipient,
        string $event,
        array $payload,
        bool $success,
        ?string $notifiableType = null,
        ?int $notifiableId = null,
    ): NotificationLog {
        return NotificationLog::create([
            'channel' => $channel,
            'recipient' => $recipient,
            'subject' => $event,
            'body' => $payload['message'] ?? json_encode($payload),
            'payload' => $payload,
            'status' => $success ? 'sent' : 'failed',
            'sent_at' => $success ? now() : null,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
        ]);
    }

    /**
     * Execute a broadcast job — send to all matching recipients.
     */
    public function executeBroadcast(BroadcastJob $job): void
    {
        $template = $job->template;
        $filter = $job->filter_criteria ?? [];

        // Build recipient query
        $query = ErpAccount::where('is_active', true)->whereNotNull('phone');

        if (!empty($filter['role'])) {
            $query->role($filter['role']);
        }

        $recipients = $query->get();
        $job->update(['total_recipients' => $recipients->count()]);

        $sent = 0;
        $failed = 0;

        foreach ($recipients as $account) {
            $vars = [
                'name' => $account->full_name,
                'phone' => $account->phone,
                'username' => $account->username,
            ];

            $log = $this->sendNotification(
                $template,
                $account->phone,
                $vars,
                ErpAccount::class,
                $account->id,
            );

            if ($log->status === 'sent') {
                $sent++;
            } else {
                $failed++;
            }

            // Rate limiting: 1 second between messages
            usleep(1000000);
        }

        $job->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
