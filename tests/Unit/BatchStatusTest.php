<?php

namespace Tests\Unit;

use App\Enums\BatchStatus;
use App\Models\Batch;
use App\Support\Quantity;
use Tests\TestCase;

/**
 * Status derivation rules. Uses unsaved models, so no database is needed
 * (Tests\TestCase still boots the app for today() and config()).
 */
class BatchStatusTest extends TestCase
{
    private function batch(array $attributes = []): Batch
    {
        return (new Batch)->forceFill(array_merge([
            'manufacturing_date' => today()->subMonth(),
            'expiry_date' => today()->addMonths(6),
            'initial_quantity' => '100.000',
            'current_quantity' => '100.000',
            'recalled_at' => null,
        ], $attributes));
    }

    public function test_a_batch_with_stock_before_its_expiry_date_is_active(): void
    {
        $this->assertSame(BatchStatus::Active, $this->batch()->status);
        $this->assertSame(BatchStatus::Active, $this->batch(['expiry_date' => null])->status);
    }

    public function test_a_batch_is_still_active_on_its_expiry_date(): void
    {
        $this->assertSame(BatchStatus::Active, $this->batch(['expiry_date' => today()])->status);
    }

    public function test_a_batch_is_expired_the_day_after_its_expiry_date(): void
    {
        $this->assertSame(BatchStatus::Expired, $this->batch(['expiry_date' => today()->subDay()])->status);
    }

    public function test_a_batch_with_no_stock_is_depleted_even_if_expired(): void
    {
        $this->assertSame(BatchStatus::Depleted, $this->batch(['current_quantity' => '0.000'])->status);
        $this->assertSame(BatchStatus::Depleted, $this->batch([
            'current_quantity' => '0.000',
            'expiry_date' => today()->subDay(),
        ])->status);
    }

    public function test_a_recall_takes_precedence_over_every_other_state(): void
    {
        $this->assertSame(BatchStatus::Recalled, $this->batch([
            'recalled_at' => now(),
            'current_quantity' => '0.000',
            'expiry_date' => today()->subDay(),
        ])->status);
    }

    public function test_approaching_expiry_uses_the_configured_window(): void
    {
        config(['productsphere.expiry_warning_days' => 30]);

        $this->assertTrue($this->batch(['expiry_date' => today()->addDays(30)])->isApproachingExpiry());
        $this->assertFalse($this->batch(['expiry_date' => today()->addDays(31)])->isApproachingExpiry());
        $this->assertFalse($this->batch(['expiry_date' => today()->subDay()])->isApproachingExpiry());
        $this->assertFalse($this->batch(['expiry_date' => null])->isApproachingExpiry());
    }

    public function test_quantities_are_formatted_without_float_conversion(): void
    {
        $this->assertSame('1,250.5', Quantity::format('1250.500'));
        $this->assertSame('99,999,999,999.999', Quantity::format('99999999999.999'));
        $this->assertSame('0', Quantity::format('0.000'));
        $this->assertSame('—', Quantity::format(null));
    }
}
