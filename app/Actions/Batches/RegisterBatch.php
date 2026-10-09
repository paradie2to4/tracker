<?php

namespace App\Actions\Batches;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\User;
use App\Services\StockLedger;
use Illuminate\Support\Facades\DB;

/**
 * Registers a batch and places its full initial quantity at the origin
 * location. Batch row, stock balance and "production" movement are created
 * in one transaction: there is never a batch without a ledger entry.
 */
final class RegisterBatch
{
    public function __construct(private readonly StockLedger $ledger) {}

    /**
     * @param  array{product_id: int, origin_location_id: int, batch_number: string, manufacturing_date: string, expiry_date?: string|null, initial_quantity: string}  $data
     */
    public function handle(array $data, User $actor): Batch
    {
        return DB::transaction(function () use ($data, $actor) {
            $quantity = StockLedger::normalise($data['initial_quantity']);
            $origin = Location::findOrFail($data['origin_location_id']);

            $batch = Batch::create([
                ...$data,
                'initial_quantity' => $quantity,
                'current_quantity' => $quantity,
            ]);

            $this->ledger->credit($batch, $origin, $quantity);
            $this->ledger->record(MovementType::Production, $batch, $quantity, [
                'to_location_id' => $origin->getKey(),
            ], $actor);

            return $batch;
        });
    }
}
