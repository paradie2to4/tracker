<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Writes entries to the append-only audit trail.
 *
 * Call it inside the same database transaction as the change being
 * audited, so the change and its audit entry are committed (or rolled
 * back) together.
 */
final class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    public static function record(
        string $event,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null,
    ): AuditLog {
        $actor ??= auth()->user();

        return AuditLog::create([
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'user_id' => $actor?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => app()->runningInConsole() ? null : request()->ip(),
        ]);
    }
}
