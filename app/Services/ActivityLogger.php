<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $action,
        string $module,
        ?Model $reference = null,
        ?string $description = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->getKey(),
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 255),
        ]);
    }
}
