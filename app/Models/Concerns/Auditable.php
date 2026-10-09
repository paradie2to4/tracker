<?php

namespace App\Models\Concerns;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Records "<model>.created" and "<model>.updated" audit entries with the
 * attributes that changed. Domain events such as "shipment.dispatched" are
 * recorded explicitly by the action classes instead.
 *
 * Changes made with query-builder updates (e.g. stock quantities maintained
 * by StockLedger) bypass model events on purpose: the stock ledger is
 * already the complete history of those values.
 */
trait Auditable
{
    /**
     * Attributes that are never written to the audit trail.
     *
     * @var list<string>
     */
    protected static array $auditExclude = ['created_at', 'updated_at', 'password', 'remember_token'];

    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            AuditLogger::record(
                $model->auditEventPrefix().'.created',
                $model,
                null,
                Arr::except($model->getAttributes(), static::$auditExclude),
            );
        });

        static::updated(function (Model $model) {
            $changes = Arr::except($model->getChanges(), static::$auditExclude);

            if ($changes === []) {
                return;
            }

            AuditLogger::record(
                $model->auditEventPrefix().'.updated',
                $model,
                Arr::only($model->getRawOriginal(), array_keys($changes)),
                $changes,
            );
        });
    }

    public function auditEventPrefix(): string
    {
        return Str::snake(class_basename($this));
    }
}
