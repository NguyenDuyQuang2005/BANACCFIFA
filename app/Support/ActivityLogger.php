<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes the admin audit trail. Never put passwords or game-account credentials in $properties.
 */
class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(string $action, string $description, ?Model $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => request()->ip(),
        ]);
    }
}
