<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogger
{
    public static function log(Request $request, string $actionType, string $description, ?string $modelType = null, ?int $modelId = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => optional($request->user())->id,
            'action_type' => $actionType,
            'description' => $description,
            'ip_address' => $request->ip(),
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);
    }
}
