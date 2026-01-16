<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Webhook;
use Illuminate\Support\Str;

class WebhookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example webhooks - uncomment to use
        // These would be configured by admins in production

        /*
        $events = [
            'case.created',
            'case.status_changed',
            'case.inspection_completed',
            'inspection.document_added',
        ];

        foreach ($events as $event) {
            Webhook::firstOrCreate(
                ['event' => $event],
                [
                    'url' => config('app.webhook_endpoint', 'http://localhost/webhooks/receive'),
                    'secret' => Str::random(32),
                    'active' => false, // Disabled by default
                ]
            );
        }
        */
    }
}
