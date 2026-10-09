<?php

namespace App\Models;

use App\Enums\MovementType;
use App\Enums\RemovalReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One entry in the append-only stock ledger. Created only by
 * App\Services\StockLedger; PostgreSQL rejects UPDATE/DELETE with a trigger.
 */
class StockMovement extends Model
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
            'type' => MovementType::class,
            'removal_reason' => RemovalReason::class,
            'quantity' => 'decimal:3',
            'occurred_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stock movements are append-only. Record a correcting movement instead.'));
        static::deleting(fn () => throw new LogicException('Stock movements are append-only and cannot be deleted.'));
    }

    /**
     * @return BelongsTo<Batch, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    /**
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The location whose balance this movement changed.
     */
    public function affectedLocation(): ?Location
    {
        return $this->type->direction() > 0 ? $this->toLocation : $this->fromLocation;
    }
}
