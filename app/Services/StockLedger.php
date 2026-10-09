<?php

namespace App\Services;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use App\Support\Quantity;
use Brick\Math\BigDecimal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Low-level stock operations. Every method must run inside a database
 * transaction opened by the calling action, so a movement and the balance
 * change it explains are committed together or not at all.
 *
 * Locking order (to avoid deadlocks between concurrent operations):
 * shipment row, then batch rows by ascending ID, then balance rows.
 */
final class StockLedger
{
    /**
     * Lock the given batches (SELECT ... FOR UPDATE) in ascending ID order.
     *
     * @param  array<int, int>  $batchIds
     * @return Collection<int, Batch>
     */
    public function lockBatches(array $batchIds): Collection
    {
        $this->assertInTransaction();

        return Batch::whereKey($batchIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * Add stock to a location. The upsert is atomic, so two concurrent
     * credits to a balance that does not exist yet cannot both insert it.
     */
    public function credit(Batch $batch, Location $location, string $quantity): void
    {
        $this->assertInTransaction();

        $now = now();

        DB::table('stock_balances')->upsert(
            [[
                'batch_id' => $batch->getKey(),
                'location_id' => $location->getKey(),
                'quantity' => $quantity,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['batch_id', 'location_id'],
            [
                'quantity' => DB::raw('stock_balances.quantity + excluded.quantity'),
                'updated_at' => $now,
            ],
        );
    }

    /**
     * Remove stock from a location, refusing to go below zero.
     *
     * The balance row is locked first, so a concurrent debit waits and then
     * sees the reduced quantity instead of spending the same stock twice.
     *
     * @param  string  $errorKey  Form field that receives the error message.
     *
     * @throws ValidationException
     */
    public function debit(Batch $batch, Location $location, string $quantity, string $errorKey = 'quantity'): void
    {
        $this->assertInTransaction();

        $balance = StockBalance::query()
            ->where('batch_id', $batch->getKey())
            ->where('location_id', $location->getKey())
            ->lockForUpdate()
            ->first();

        $available = $balance?->quantity ?? '0.000';

        if (BigDecimal::of($available)->isLessThan($quantity)) {
            throw ValidationException::withMessages([
                $errorKey => sprintf(
                    'Only %s of batch %s is available at %s.',
                    Quantity::format($available),
                    $batch->batch_number,
                    $location->name,
                ),
            ]);
        }

        // Arithmetic happens in SQL on NUMERIC values: exact, no floats.
        StockBalance::whereKey($balance->getKey())->decrement('quantity', $quantity);
    }

    /**
     * Append a movement to the ledger.
     *
     * @param  array{from_location_id?: int|null, to_location_id?: int|null, shipment_id?: int|null, removal_reason?: string|null, notes?: string|null}  $attributes
     */
    public function record(MovementType $type, Batch $batch, string $quantity, array $attributes, ?User $actor): StockMovement
    {
        $this->assertInTransaction();

        $movement = new StockMovement;
        $movement->forceFill([
            'batch_id' => $batch->getKey(),
            'type' => $type,
            'quantity' => $quantity,
            'from_location_id' => $attributes['from_location_id'] ?? null,
            'to_location_id' => $attributes['to_location_id'] ?? null,
            'shipment_id' => $attributes['shipment_id'] ?? null,
            'removal_reason' => $attributes['removal_reason'] ?? null,
            'notes' => $attributes['notes'] ?? null,
            'user_id' => $actor?->getKey(),
            'occurred_at' => now(),
        ])->save();

        return $movement;
    }

    /**
     * Normalise a validated quantity to the database scale, e.g. "12.5" -> "12.500".
     */
    public static function normalise(string|int|float $quantity): string
    {
        return (string) BigDecimal::of((string) $quantity)->toScale(3);
    }

    private function assertInTransaction(): void
    {
        if (DB::transactionLevel() === 0) {
            throw new LogicException('Stock operations must run inside a database transaction.');
        }
    }
}
