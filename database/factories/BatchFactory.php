<?php

namespace Database\Factories;

use App\Enums\MovementType;
use App\Enums\RemovalReason;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockMovement;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * Dates are relative to today so the default state is always "active",
 * far enough from expiry not to trigger warnings. Use the named states for
 * expired, expiring, recalled and depleted batches.
 *
 * Created batches get a ledger consistent with their quantities: a
 * production movement at the origin, a stock balance holding the current
 * quantity, and a removal for any difference. Use withoutOrigin() for a
 * batch registered before stock tracking existed.
 *
 * @extends Factory<Batch>
 */
class BatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(50, 5000).'.000';

        return [
            'product_id' => Product::factory(),
            'origin_location_id' => Location::factory(),
            'batch_number' => 'BN-'.fake()->unique()->numerify('########'),
            'manufacturing_date' => today()->subDays(fake()->numberBetween(7, 180))->toDateString(),
            'expiry_date' => today()->addDays(fake()->numberBetween(90, 720))->toDateString(),
            'initial_quantity' => $quantity,
            'current_quantity' => $quantity,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Batch $batch) {
            if ($batch->origin_location_id === null) {
                return;
            }

            $occurredAt = $batch->manufacturing_date;
            $removed = BigDecimal::of($batch->initial_quantity)->minus($batch->current_quantity);

            $production = new StockMovement;
            $production->forceFill([
                'batch_id' => $batch->id,
                'type' => MovementType::Production,
                'quantity' => $batch->initial_quantity,
                'to_location_id' => $batch->origin_location_id,
                'occurred_at' => $occurredAt,
            ])->save();

            if ($removed->isPositive()) {
                $removal = new StockMovement;
                $removal->forceFill([
                    'batch_id' => $batch->id,
                    'type' => MovementType::Removal,
                    'quantity' => (string) $removed,
                    'from_location_id' => $batch->origin_location_id,
                    'removal_reason' => RemovalReason::Sold,
                    'occurred_at' => $occurredAt->copy()->addDay(),
                ])->save();
            }

            DB::table('stock_balances')->insert([
                'batch_id' => $batch->id,
                'location_id' => $batch->origin_location_id,
                'quantity' => $batch->current_quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * A batch registered before Phase 6: no location and no ledger entries.
     */
    public function withoutOrigin(): static
    {
        return $this->state(fn (array $attributes) => [
            'origin_location_id' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'manufacturing_date' => today()->subDays(fake()->numberBetween(400, 700))->toDateString(),
            'expiry_date' => today()->subDays(fake()->numberBetween(1, 60))->toDateString(),
        ]);
    }

    /**
     * Expires within the default 30-day warning window.
     */
    public function expiringSoon(int $inDays = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => today()->addDays($inDays)->toDateString(),
        ]);
    }

    public function withoutExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => null,
        ]);
    }

    public function depleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_quantity' => '0.000',
        ]);
    }

    public function recalled(string $reason = 'Contamination reported during quality inspection.'): static
    {
        return $this->state(fn (array $attributes) => [
            'recalled_at' => now(),
            'recall_reason' => $reason,
        ]);
    }
}
