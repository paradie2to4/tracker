<?php

namespace App\Actions\Shipments;

use App\Enums\MovementType;
use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Cancels a shipment that is still in transit and returns every item to
 * the origin location. The original dispatch movements stay in the ledger;
 * new "cancellation_return" movements reverse them.
 */
final class CancelShipment
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @throws ValidationException
     */
    public function handle(Shipment $shipment, string $reason, User $actor): Shipment
    {
        return DB::transaction(function () use ($shipment, $reason, $actor) {
            $locked = Shipment::whereKey($shipment->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isInTransit()) {
                throw ValidationException::withMessages([
                    'shipment' => "This shipment is already {$locked->status->label()} and cannot be cancelled.",
                ]);
            }

            $locked->load(['items' => fn ($query) => $query->orderBy('batch_id'), 'items.batch', 'fromLocation']);

            foreach ($locked->items as $item) {
                $this->ledger->credit($item->batch, $locked->fromLocation, $item->quantity);
                $this->ledger->record(MovementType::CancellationReturn, $item->batch, $item->quantity, [
                    'to_location_id' => $locked->from_location_id,
                    'shipment_id' => $locked->getKey(),
                    'notes' => $reason,
                ], $actor);
            }

            $locked->forceFill([
                'status' => ShipmentStatus::Cancelled,
                'cancelled_at' => now(),
                'cancelled_by' => $actor->getKey(),
                'cancellation_reason' => $reason,
            ])->save();

            AuditLogger::record('shipment.cancelled', $locked, ['status' => ShipmentStatus::InTransit->value], [
                'status' => ShipmentStatus::Cancelled->value,
                'reason' => $reason,
            ], $actor);

            return $locked;
        });
    }
}
