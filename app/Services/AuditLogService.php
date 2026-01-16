<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log($action, $model, $changes = null)
    {
        if (!Auth::check()) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        if ($changes) {
            foreach ($changes as $key => $value) {
                $oldValues[$key] = $model->getOriginal($key) ?? null;
                $newValues[$key] = $value;
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => class_basename($model),
            'model_id' => $model->id,
            'changes' => [
                'old' => $oldValues,
                'new' => $newValues,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    public static function getLogs($modelType, $modelId, $limit = 50)
    {
        return AuditLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
