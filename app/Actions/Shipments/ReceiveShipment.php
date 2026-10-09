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
 * Confirms delivery: credits every item to the destination location.
 * The full dispatched quantity is received; partial receipts and
 * discrepancy reporting are planned for a later phase.
 */
final class ReceiveShipment
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @throws ValidationException
     */
    public function handle(Shipment $shipment, User $actor): Shipment
    {
        return DB::transaction(function () use ($shipment, $actor) {
            // Locking the shipment row makes the status check and the
            // transition atomic: two people pressing "Receive" at the same
            // moment cannot both credit the stock.
            $locked = Shipment::whereKey($shipment->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->isInTransit()) {
                throw ValidationException::withMessages([
                    'shipment' => "This shipment is already {$locked->status->label()} and cannot be received.",
                ]);
            }

            $locked->load(['items' => fn ($query) => $query->orderBy('batch_id'), 'items.batch', 'toLocation']);

            foreach ($locked->items as $item) {
                $this->ledger->credit($item->batch, $locked->toLocation, $item->quantity);
                $this->ledger->record(MovementType::Receipt, $item->batch, $item->quantity, [
                    'to_location_id' => $locked->to_location_id,
                    'shipment_id' => $locked->getKey(),
                ], $actor);
            }

            $locked->forceFill([
                'status' => ShipmentStatus::Received,
                'received_at' => now(),
                'received_by' => $actor->getKey(),
            ])->save();

            AuditLogger::record('shipment.received', $locked, ['status' => ShipmentStatus::InTransit->value], ['status' => ShipmentStatus::Received->value], $actor);

            return $locked;
        });
    }
}
