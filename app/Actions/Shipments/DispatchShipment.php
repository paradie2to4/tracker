<?php

namespace App\Actions\Shipments;

use App\Enums\BatchStatus;
use App\Enums\MovementType;
use App\Enums\ShipmentStatus;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sends stock from one location to another. The stock leaves the origin
 * immediately and is "in transit" until the shipment is received or
 * cancelled. If any item is invalid, nothing is dispatched.
 */
final class DispatchShipment
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @param  array<int, string>  $quantities  batch_id => quantity
     *
     * @throws ValidationException
     */
    public function handle(Location $from, Location $to, array $quantities, ?string $notes, User $actor): Shipment
    {
        if ($quantities === []) {
            throw ValidationException::withMessages(['items' => 'Enter a quantity for at least one batch.']);
        }

        if ($from->is($to)) {
            throw ValidationException::withMessages(['to_location_id' => 'The destination must be different from the origin.']);
        }

        return DB::transaction(function () use ($from, $to, $quantities, $notes, $actor) {
            // FOR SHARE: blocks a concurrent deactivation of either location
            // without serialising unrelated dispatches from the same place.
            $locations = Location::whereKey([$from->getKey(), $to->getKey()])->sharedLock()->get()->keyBy('id');

            foreach (['from_location_id' => $from, 'to_location_id' => $to] as $field => $location) {
                if (! $locations->get($location->getKey())?->is_active) {
                    throw ValidationException::withMessages([$field => "{$location->name} is inactive and cannot send or receive shipments."]);
                }
            }

            ksort($quantities);
            $batches = $this->ledger->lockBatches(array_keys($quantities));

            foreach ($quantities as $batchId => $quantity) {
                $field = "items.{$batchId}.quantity";
                $batch = $batches->get($batchId)
                    ?? throw ValidationException::withMessages([$field => 'This batch no longer exists.']);

                if ($batch->status === BatchStatus::Recalled) {
                    throw ValidationException::withMessages([$field => "Batch {$batch->batch_number} has been recalled and cannot be shipped."]);
                }

                if ($batch->status === BatchStatus::Expired) {
                    throw ValidationException::withMessages([$field => "Batch {$batch->batch_number} has expired and cannot be shipped."]);
                }

                $quantities[$batchId] = StockLedger::normalise($quantity);
                $this->ledger->debit($batch, $from, $quantities[$batchId], $field);
            }

            $shipment = new Shipment;
            $shipment->forceFill([
                'from_location_id' => $from->getKey(),
                'to_location_id' => $to->getKey(),
                'status' => ShipmentStatus::InTransit,
                'notes' => $notes,
                'dispatched_at' => now(),
                'dispatched_by' => $actor->getKey(),
            ])->save();

            foreach ($quantities as $batchId => $quantity) {
                $item = new ShipmentItem;
                $item->forceFill([
                    'shipment_id' => $shipment->getKey(),
                    'batch_id' => $batchId,
                    'quantity' => $quantity,
                ])->save();

                $this->ledger->record(MovementType::Dispatch, $batches->get($batchId), $quantity, [
                    'from_location_id' => $from->getKey(),
                    'shipment_id' => $shipment->getKey(),
                ], $actor);
            }

            AuditLogger::record('shipment.dispatched', $shipment, null, [
                'from_location_id' => $from->getKey(),
                'to_location_id' => $to->getKey(),
                'items' => $quantities,
            ], $actor);

            return $shipment;
        });
    }
}
