<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Enums\MovementType;
use App\Enums\RemovalReason;
use App\Models\Batch;
use App\Models\Location;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRemovalTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Location $shop;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create();
        $this->shop = Location::factory()->create();
        $this->batch = Batch::factory()->create([
            'origin_location_id' => $this->shop->id,
            'initial_quantity' => '20.000',
            'current_quantity' => '20.000',
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function remove(array $overrides = [], ?Batch $batch = null): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->staff)
            ->post(route('batches.removals.store', $batch ?? $this->batch), array_merge([
                'location_id' => $this->shop->id,
                'quantity' => '4.5',
                'reason' => RemovalReason::Sold->value,
            ], $overrides));
    }

    public function test_removing_stock_reduces_the_location_and_batch_totals(): void
    {
        $this->remove()->assertRedirect(route('batches.show', $this->batch))->assertSessionHas('success');

        $this->assertSame('15.500', $this->batch->fresh()->current_quantity);
        $this->assertSame('15.500', StockBalance::where('batch_id', $this->batch->id)->sole()->quantity);

        $movement = StockMovement::where('type', MovementType::Removal)->sole();
        $this->assertSame(RemovalReason::Sold, $movement->removal_reason);
        $this->assertSame($this->staff->id, $movement->user_id);
    }

    public function test_removing_all_stock_makes_the_batch_depleted(): void
    {
        $this->remove(['quantity' => '20']);

        $this->assertSame(BatchStatus::Depleted, $this->batch->fresh()->status);
    }

    public function test_more_than_the_stock_at_the_location_cannot_be_removed(): void
    {
        $this->remove(['quantity' => '20.001'])->assertSessionHasErrors('quantity');

        $this->assertSame('20.000', $this->batch->fresh()->current_quantity);
        $this->assertSame(0, StockMovement::where('type', MovementType::Removal)->count());
    }

    public function test_stock_cannot_be_removed_from_a_location_that_does_not_hold_it(): void
    {
        $this->remove(['location_id' => Location::factory()->create()->id, 'quantity' => '1'])
            ->assertSessionHasErrors('quantity');

        $this->assertSame('20.000', $this->batch->fresh()->current_quantity);
    }

    public function test_losses_and_corrections_must_be_explained(): void
    {
        $this->remove(['reason' => RemovalReason::Lost->value])->assertSessionHasErrors('notes');
        $this->remove(['reason' => RemovalReason::Correction->value])->assertSessionHasErrors('notes');

        $this->remove(['reason' => RemovalReason::Lost->value, 'notes' => 'Carton missing after stock count.'])
            ->assertSessionHasNoErrors();
    }

    public function test_recalled_stock_can_still_be_disposed_of(): void
    {
        $recalled = Batch::factory()->recalled()->create(['origin_location_id' => $this->shop->id, 'initial_quantity' => '8.000', 'current_quantity' => '8.000']);

        $this->remove(['quantity' => '8', 'reason' => RemovalReason::Disposed->value], $recalled)
            ->assertSessionHasNoErrors();

        $this->assertSame('0.000', $recalled->fresh()->current_quantity);
    }

    public function test_invalid_quantities_are_rejected(): void
    {
        foreach (['0', '-1', '1.2345', 'abc'] as $quantity) {
            $this->remove(['quantity' => $quantity])->assertSessionHasErrors('quantity');
        }

        $this->assertSame('20.000', $this->batch->fresh()->current_quantity);
    }

    public function test_legacy_batches_need_opening_stock_before_they_can_move(): void
    {
        $legacy = Batch::factory()->withoutOrigin()->create(['initial_quantity' => '12.000', 'current_quantity' => '12.000']);

        $this->actingAs($this->staff)
            ->post(route('batches.opening-stock.store', $legacy), ['location_id' => $this->shop->id])
            ->assertForbidden();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('batches.show', $legacy))
            ->assertOk()
            ->assertSee('Stock location not recorded');

        $this->actingAs($admin)
            ->post(route('batches.opening-stock.store', $legacy), ['location_id' => $this->shop->id])
            ->assertRedirect(route('batches.show', $legacy));

        $legacy->refresh();
        $this->assertSame($this->shop->id, $legacy->origin_location_id);
        $this->assertSame('12.000', StockBalance::where('batch_id', $legacy->id)->sole()->quantity);
        $this->assertSame(MovementType::Production, StockMovement::where('batch_id', $legacy->id)->sole()->type);

        // It can only be done once.
        $this->actingAs($admin)
            ->post(route('batches.opening-stock.store', $legacy), ['location_id' => $this->shop->id])
            ->assertForbidden();
    }
}
