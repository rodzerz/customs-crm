<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    public static function dispatch($event, $payload)
    {
        $webhooks = Webhook::where('event', $event)
            ->where('active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            self::send($webhook, $event, $payload);
        }
    }

    public static function send(Webhook $webhook, $event, $payload)
    {
        try {
            $signature = $webhook->generateSignature($payload);

            $response = Http::withHeaders([
                'X-Signature' => $signature,
                'X-Event' => $event,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($webhook->url, $payload);

            $success = $response->successful();

            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'status_code' => $response->status(),
                'payload' => $payload,
                'response' => $response->body(),
                'success' => $success,
            ]);

            if ($success) {
                $webhook->update([
                    'last_triggered_at' => now(),
                    'retry_count' => 0,
                ]);
            }
        } catch (\Exception $e) {
            $webhook->increment('retry_count');

            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => $event,
                'payload' => $payload,
                'error' => $e->getMessage(),
                'success' => false,
            ]);
        }
    }
}
