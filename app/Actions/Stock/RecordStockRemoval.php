<?php

namespace App\Actions\Stock;

use App\Enums\MovementType;
use App\Enums\RemovalReason;
use App\Models\Batch;
use App\Models\Location;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\StockLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Takes stock permanently out of the supply chain (sold to consumers, used,
 * damaged, disposed of...). Reduces both the location balance and the
 * batch's total current_quantity.
 *
 * Allowed for recalled and expired batches too: disposing of that stock
 * is exactly what should happen to it.
 */
final class RecordStockRemoval
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @throws ValidationException
     */
    public function handle(Batch $batch, Location $location, string $quantity, RemovalReason $reason, ?string $notes, User $actor): Batch
    {
        return DB::transaction(function () use ($batch, $location, $quantity, $reason, $notes, $actor) {
            $quantity = StockLedger::normalise($quantity);
            $locked = $this->ledger->lockBatches([$batch->getKey()])->firstOrFail();

            $this->ledger->debit($locked, $location, $quantity);

            Batch::whereKey($locked->getKey())->decrement('current_quantity', $quantity);

            $movement = $this->ledger->record(MovementType::Removal, $locked, $quantity, [
                'from_location_id' => $location->getKey(),
                'removal_reason' => $reason->value,
                'notes' => $notes,
            ], $actor);

            AuditLogger::record('stock.removed', $locked, null, [
                'movement_id' => $movement->getKey(),
                'location_id' => $location->getKey(),
                'quantity' => $quantity,
                'reason' => $reason->value,
            ], $actor);

            return $locked->refresh();
        });
    }
}
