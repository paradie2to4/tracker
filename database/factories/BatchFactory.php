<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Dates are relative to today so the default state is always "active",
 * far enough from expiry not to trigger warnings. Use the named states for
 * expired, expiring, recalled and depleted batches.
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
            'batch_number' => 'BN-'.fake()->unique()->numerify('########'),
            'manufacturing_date' => today()->subDays(fake()->numberBetween(7, 180))->toDateString(),
            'expiry_date' => today()->addDays(fake()->numberBetween(90, 720))->toDateString(),
            'initial_quantity' => $quantity,
            'current_quantity' => $quantity,
        ];
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
