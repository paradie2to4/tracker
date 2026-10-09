<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A consignment of one or more batches between two locations.
 * Created and transitioned only by the actions in App\Actions\Shipments,
 * which is why there is no fillable list.
 */
class Shipment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ShipmentStatus::class,
            'dispatched_at' => 'datetime',
            'received_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Human-readable reference derived from the ID, e.g. SHP-000042.
     * Derived rather than stored, so it can never disagree with the ID.
     *
     * @return Attribute<string, never>
     */
    protected function reference(): Attribute
    {
        return Attribute::get(fn (): string => self::formatReference($this->getKey()));
    }

    public static function formatReference(int $id): string
    {
        return 'SHP-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parse "SHP-000042", "shp-42" or "42" back into an ID.
     */
    public static function idFromReference(string $reference): ?int
    {
        return preg_match('/^(?:SHP-?)?0*(\d{1,9})$/i', trim($reference), $matches) === 1
            ? (int) $matches[1]
            : null;
    }

    public function isInTransit(): bool
    {
        return $this->status === ShipmentStatus::InTransit;
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
     * @return HasMany<ShipmentItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
