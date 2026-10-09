<?php

namespace App\Models;

use App\Support\Quantity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use LogicException;

/**
 * Append-only: entries can be created but never changed or deleted.
 * PostgreSQL enforces the same rule with a trigger.
 */
#[Fillable(['event', 'subject_type', 'subject_id', 'user_id', 'old_values', 'new_values', 'ip_address'])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit log entries are append-only and cannot be modified.'));
        static::deleting(fn () => throw new LogicException('Audit log entries are append-only and cannot be deleted.'));
    }

    /**
     * Fields that are internal plumbing rather than something a reader cares about.
     *
     * @var list<string>
     */
    private const HIDDEN_FIELDS = ['id', 'movement_id', 'email_verified_at'];

    /**
     * Fields whose stored values are enum keys that read better as words.
     *
     * @var list<string>
     */
    private const WORD_FIELDS = ['type', 'category', 'status', 'role', 'reason', 'removal_reason'];

    /**
     * Readable phrases for each event, e.g. "created location".
     *
     * @var array<string, string>
     */
    private const PHRASES = [
        'shipment.dispatched' => 'dispatched shipment',
        'shipment.received' => 'received shipment',
        'shipment.cancelled' => 'cancelled shipment',
        'batch.recalled' => 'recalled batch',
        'batch.opening_stock_assigned' => 'assigned opening stock to batch',
        'stock.removed' => 'removed stock from batch',
    ];

    /**
     * Event family, e.g. "shipment" for "shipment.received".
     */
    public function group(): string
    {
        return Str::before($this->event, '.');
    }

    /**
     * Verb phrase for the event, e.g. "created location" or "recalled batch".
     */
    public function phrase(): string
    {
        if (isset(self::PHRASES[$this->event])) {
            return self::PHRASES[$this->event];
        }

        $action = Str::after($this->event, '.');

        return Str::of($action)->replace('_', ' ')->append(' ', Str::of($this->group())->replace('_', ' '))->toString();
    }

    public function isCreation(): bool
    {
        return Str::endsWith($this->event, '.created');
    }

    /**
     * Name of the record the entry is about, e.g. "Gikondo" or "SHP-000004".
     */
    public function subjectLabel(): ?string
    {
        $subject = $this->subject;

        return match (true) {
            $subject instanceof Shipment => $subject->reference,
            $subject instanceof Batch => $subject->batch_number,
            $subject instanceof Product, $subject instanceof Organization, $subject instanceof Location, $subject instanceof User => $subject->name,
            default => $this->subject_type ? Str::headline($this->subject_type).' #'.$this->subject_id : null,
        };
    }

    /**
     * Page of the record the entry is about, when it has one.
     */
    public function subjectUrl(): ?string
    {
        $subject = $this->subject;

        return match (true) {
            $subject instanceof Shipment => route('shipments.show', $subject),
            $subject instanceof Batch => route('batches.show', $subject),
            $subject instanceof Product => route('products.show', $subject),
            $subject instanceof Organization => route('organizations.show', $subject),
            $subject instanceof Location => route('locations.show', $subject),
            default => null,
        };
    }

    /**
     * Changes ready for display: readable labels, formatted values, and
     * internal fields (IDs, foreign keys) left out.
     *
     * @return list<array{label: string, old: string|null, new: string}>
     */
    public function displayChanges(): array
    {
        $old = $this->old_values ?? [];

        return collect($this->new_values ?? [])
            ->reject(fn ($value, string $field) => in_array($field, self::HIDDEN_FIELDS, true) || Str::endsWith($field, '_id'))
            ->map(fn ($value, string $field) => [
                'label' => $field === 'tin' ? 'TIN' : Str::of($field)->replace('_', ' ')->ucfirst()->toString(),
                'old' => array_key_exists($field, $old) ? self::formatValue($field, $old[$field]) : null,
                'new' => self::formatValue($field, $value),
            ])
            ->values()
            ->all();
    }

    private static function formatValue(string $field, mixed $value): string
    {
        return match (true) {
            $value === null || $value === '' => '—',
            Str::startsWith($field, 'is_') => in_array($value, [true, 1, '1'], true) ? 'Yes' : 'No',
            is_bool($value) => $value ? 'Yes' : 'No',
            $field === 'items' && is_array($value) => count($value).' '.Str::plural('batch', count($value)),
            Str::endsWith($field, 'quantity') && is_numeric($value) => Quantity::format((string) $value),
            in_array($field, self::WORD_FIELDS, true) && is_string($value) => Str::of($value)->replace('_', ' ')->ucfirst()->toString(),
            is_scalar($value) => (string) $value,
            default => (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        };
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
