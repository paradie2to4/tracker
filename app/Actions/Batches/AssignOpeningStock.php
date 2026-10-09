<?php

namespace App\Actions\Batches;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * One-time migration path for batches registered before stock tracking
 * (Phase 6). Places the batch's current quantity at a location as an
 * opening "production" movement, after which it behaves like any batch.
 */
final class AssignOpeningStock
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @throws ValidationException
     */
    public function handle(Batch $batch, Location $location, User $actor): Batch
    {
        return DB::transaction(function () use ($batch, $location, $actor) {
            $locked = $this->ledger->lockBatches([$batch->getKey()])->firstOrFail();

            if ($locked->origin_location_id !== null || $locked->stockMovements()->exists()) {
                throw ValidationException::withMessages(['location_id' => 'This batch already has stock tracking.']);
            }

            if (! $location->is_active) {
                throw ValidationException::withMessages(['location_id' => 'Select an active location.']);
            }

            $locked->origin_location_id = $location->getKey();
            $locked->save();

            if (! $locked->isEmpty()) {
                $this->ledger->credit($locked, $location, $locked->current_quantity);
                $this->ledger->record(MovementType::Production, $locked, $locked->current_quantity, [
                    'to_location_id' => $location->getKey(),
                    'notes' => 'Opening balance for a batch registered before stock tracking.',
                ], $actor);
            }

            AuditLogger::record('batch.opening_stock_assigned', $locked, null, [
                'location_id' => $location->getKey(),
                'quantity' => $locked->current_quantity,
            ], $actor);

            return $locked;
        });
    }
}
