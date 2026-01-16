<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\CaseEvent;
use Illuminate\Support\Facades\Auth;

class CaseEventService
{
    public static function logEvent(CaseModel $case, $eventType, $data = null, $description = null)
    {
        CaseEvent::create([
            'case_id' => $case->id,
            'user_id' => Auth::id(),
            'event_type' => $eventType,
            'data' => $data,
            'description' => $description,
        ]);

        // Trigger webhooks for this event
        WebhookService::dispatch('case.' . $eventType, [
            'case_id' => $case->id,
            'external_id' => $case->external_id,
            'status' => $case->status,
            'data' => $data,
        ]);
    }

    public static function logStatusChange(CaseModel $case, $oldStatus, $newStatus, $reason = null)
    {
        self::logEvent($case, 'status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ], $reason);
    }

    public static function getHistory(CaseModel $case)
    {
        return $case->events()->orderBy('created_at', 'desc')->get();
    }
}
