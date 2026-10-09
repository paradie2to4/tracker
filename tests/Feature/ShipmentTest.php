<?php

namespace Tests\Feature;

use App\Actions\Shipments\DispatchShipment;
use App\Actions\Shipments\ReceiveShipment;
use App\Enums\MovementType;
use App\Enums\ShipmentStatus;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Location $plant;

    private Location $warehouse;

    private Batch $batch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create();
        $this->plant = Location::factory()->create(['name' => 'Masoro plant']);
        $this->warehouse = Location::factory()->create(['name' => 'Kigali warehouse']);
        $this->batch = Batch::factory()->create([
            'origin_location_id' => $this->plant->id,
            'initial_quantity' => '100.000',
            'current_quantity' => '100.000',
        ]);
    }

    private function balance(Batch $batch, Location $location): string
    {
        return StockBalance::where('batch_id', $batch->id)->where('location_id', $location->id)->first()?->quantity ?? '0.000';
    }

    /**
     * @param  array<int, string>  $items
     */
    private function dispatch(array $items, ?Location $to = null, ?User $as = null): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($as ?? $this->staff)
            ->from(route('shipments.create', ['from' => $this->plant->id]))
            ->post(route('shipments.store'), [
                'from_location_id' => $this->plant->id,
                'to_location_id' => ($to ?? $this->warehouse)->id,
                'items' => collect($items)->map(fn ($quantity) => ['quantity' => $quantity])->all(),
            ]);
    }

    /**
     * Stock at locations + stock in transit must always equal the batch's
     * current quantity. This is the core consistency rule of the ledger.
     */
    private function assertLedgerConsistent(Batch $batch): void
    {
        $batch->refresh();

        $atLocations = StockBalance::where('batch_id', $batch->id)->get()
            ->reduce(fn (BigDecimal $carry, StockBalance $b) => $carry->plus($b->quantity), BigDecimal::zero());

        $inTransit = Shipment::where('status', ShipmentStatus::InTransit)
            ->with(['items' => fn ($query) => $query->where('batch_id', $batch->id)])
            ->get()
            ->flatMap->items
            ->reduce(fn (BigDecimal $carry, $item) => $carry->plus($item->quantity), BigDecimal::zero());

        $this->assertTrue(
            $atLocations->plus($inTransit)->isEqualTo($batch->current_quantity),
            "Ledger inconsistent: locations {$atLocations} + in transit {$inTransit} != current {$batch->current_quantity}",
        );
    }

    public function test_the_shipment_form_lists_stock_available_at_the_origin(): void
    {
        $elsewhere = Batch::factory()->create(); // stock at another location

        $this->actingAs($this->staff)
            ->get(route('shipments.create', ['from' => $this->plant->id]))
            ->assertOk()
            ->assertSee($this->batch->batch_number)
            ->assertDontSee($elsewhere->batch_number);
    }

    public function test_dispatching_moves_stock_from_the_origin_into_transit(): void
    {
        $this->dispatch([$this->batch->id => '30.5'])
            ->assertRedirect(route('shipments.show', Shipment::sole()))
            ->assertSessionHas('success');

        $shipment = Shipment::sole();
        $this->assertSame(ShipmentStatus::InTransit, $shipment->status);
        $this->assertSame($this->staff->id, $shipment->dispatched_by);
        $this->assertSame('30.500', $shipment->items()->sole()->quantity);

        $this->assertSame('69.500', $this->balance($this->batch, $this->plant));
        $this->assertSame('0.000', $this->balance($this->batch, $this->warehouse));
        // Still in the supply chain, just not at a location.
        $this->assertSame('100.000', $this->batch->fresh()->current_quantity);

        $dispatch = StockMovement::where('type', MovementType::Dispatch)->sole();
        $this->assertSame($this->plant->id, $dispatch->from_location_id);
        $this->assertSame($shipment->id, $dispatch->shipment_id);

        $this->assertLedgerConsistent($this->batch);
    }

    public function test_receiving_credits_the_destination(): void
    {
        $this->dispatch([$this->batch->id => '40']);
        $shipment = Shipment::sole();

        $this->actingAs($this->staff)
            ->post(route('shipments.receipt.store', $shipment))
            ->assertRedirect(route('shipments.show', $shipment));

        $shipment->refresh();
        $this->assertSame(ShipmentStatus::Received, $shipment->status);
        $this->assertSame($this->staff->id, $shipment->received_by);
        $this->assertNotNull($shipment->received_at);

        $this->assertSame('60.000', $this->balance($this->batch, $this->plant));
        $this->assertSame('40.000', $this->balance($this->batch, $this->warehouse));
        $this->assertSame(1, StockMovement::where('type', MovementType::Receipt)->count());

        $this->assertLedgerConsistent($this->batch);
    }

    public function test_a_shipment_cannot_be_received_twice(): void
    {
        $this->dispatch([$this->batch->id => '40']);
        $shipment = Shipment::sole();

        $this->actingAs($this->staff)->post(route('shipments.receipt.store', $shipment));
        $this->actingAs($this->staff)->post(route('shipments.receipt.store', $shipment))->assertForbidden();

        $this->assertSame('40.000', $this->balance($this->batch, $this->warehouse));
        $this->assertSame(1, StockMovement::where('type', MovementType::Receipt)->count());
    }

    public function test_cancelling_returns_the_stock_to_the_origin(): void
    {
        $this->dispatch([$this->batch->id => '25']);
        $shipment = Shipment::sole();

        $this->actingAs($this->staff)
            ->post(route('shipments.cancellation.store', $shipment), ['cancellation_reason' => 'Wrong destination selected.'])
            ->assertRedirect(route('shipments.show', $shipment));

        $shipment->refresh();
        $this->assertSame(ShipmentStatus::Cancelled, $shipment->status);
        $this->assertSame('Wrong destination selected.', $shipment->cancellation_reason);
        $this->assertSame('100.000', $this->balance($this->batch, $this->plant));
        $this->assertSame('0.000', $this->balance($this->batch, $this->warehouse));

        // History is preserved: the dispatch stays, a return reverses it.
        $this->assertSame(1, StockMovement::where('type', MovementType::Dispatch)->count());
        $this->assertSame(1, StockMovement::where('type', MovementType::CancellationReturn)->count());

        $this->assertLedgerConsistent($this->batch);
    }

    public function test_received_or_cancelled_shipments_cannot_change_state_again(): void
    {
        $this->dispatch([$this->batch->id => '10']);
        $received = Shipment::sole();
        $this->actingAs($this->staff)->post(route('shipments.receipt.store', $received));

        $this->actingAs($this->staff)
            ->post(route('shipments.cancellation.store', $received), ['cancellation_reason' => 'Trying to cancel after receipt.'])
            ->assertForbidden();

        $this->dispatch([$this->batch->id => '10']);
        $cancelled = Shipment::latest('id')->first();
        $this->actingAs($this->staff)->post(route('shipments.cancellation.store', $cancelled), ['cancellation_reason' => 'Not needed any more.']);

        $this->actingAs($this->staff)->post(route('shipments.receipt.store', $cancelled))->assertForbidden();

        $this->assertSame(ShipmentStatus::Received, $received->fresh()->status);
        $this->assertSame(ShipmentStatus::Cancelled, $cancelled->fresh()->status);
        $this->assertLedgerConsistent($this->batch);
    }

    public function test_only_the_dispatcher_or_an_administrator_can_cancel(): void
    {
        $this->dispatch([$this->batch->id => '10']);
        $shipment = Shipment::sole();
        $colleague = User::factory()->create();

        $this->actingAs($colleague)
            ->post(route('shipments.cancellation.store', $shipment), ['cancellation_reason' => 'Someone else cancelling.'])
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('shipments.cancellation.store', $shipment), ['cancellation_reason' => 'Administrator cancelling.'])
            ->assertRedirect();

        $this->assertSame(ShipmentStatus::Cancelled, $shipment->fresh()->status);
    }

    public function test_more_than_the_available_quantity_cannot_be_shipped(): void
    {
        $this->dispatch([$this->batch->id => '100.001'])
            ->assertSessionHasErrors("items.{$this->batch->id}.quantity");

        $this->assertSame(0, Shipment::count());
        $this->assertSame('100.000', $this->balance($this->batch, $this->plant));
    }

    public function test_stock_at_another_location_cannot_be_shipped_from_here(): void
    {
        $elsewhere = Batch::factory()->create();

        $this->dispatch([$elsewhere->id => '1'])
            ->assertSessionHasErrors("items.{$elsewhere->id}.quantity");

        $this->assertSame(0, Shipment::count());
    }

    public function test_recalled_and_expired_batches_cannot_be_shipped(): void
    {
        $recalled = Batch::factory()->recalled()->create(['origin_location_id' => $this->plant->id]);
        $expired = Batch::factory()->expired()->create(['origin_location_id' => $this->plant->id]);

        $this->dispatch([$recalled->id => '1'])
            ->assertSessionHasErrors(["items.{$recalled->id}.quantity" => "Batch {$recalled->batch_number} has been recalled and cannot be shipped."]);

        $this->dispatch([$expired->id => '1'])
            ->assertSessionHasErrors(["items.{$expired->id}.quantity" => "Batch {$expired->batch_number} has expired and cannot be shipped."]);

        $this->assertSame(0, Shipment::count());
    }

    public function test_a_shipment_needs_two_different_active_locations(): void
    {
        $this->dispatch([$this->batch->id => '1'], $this->plant)
            ->assertSessionHasErrors(['to_location_id' => 'The destination must be different from the origin.']);

        $closed = Location::factory()->inactive()->create();
        $this->dispatch([$this->batch->id => '1'], $closed)
            ->assertSessionHasErrors('to_location_id');

        $this->assertSame(0, Shipment::count());
    }

    public function test_at_least_one_quantity_is_required(): void
    {
        $this->dispatch([$this->batch->id => ''])
            ->assertSessionHasErrors(['items' => 'Enter a quantity for at least one batch.']);

        $this->dispatch([$this->batch->id => '-5'])
            ->assertSessionHasErrors("items.{$this->batch->id}.quantity");

        $this->assertSame(0, Shipment::count());
    }

    public function test_one_invalid_item_rolls_back_the_whole_shipment(): void
    {
        $second = Batch::factory()->create([
            'origin_location_id' => $this->plant->id,
            'initial_quantity' => '5.000',
            'current_quantity' => '5.000',
        ]);

        // The first item is fine, the second asks for more than exists.
        $this->dispatch([$this->batch->id => '10', $second->id => '6'])
            ->assertSessionHasErrors("items.{$second->id}.quantity");

        $this->assertSame(0, Shipment::count());
        $this->assertSame(0, StockMovement::where('type', MovementType::Dispatch)->count());
        $this->assertSame('100.000', $this->balance($this->batch, $this->plant));
        $this->assertSame('5.000', $this->balance($second, $this->plant));
    }

    public function test_a_multi_batch_shipment_moves_every_item(): void
    {
        $second = Batch::factory()->create(['origin_location_id' => $this->plant->id, 'initial_quantity' => '50.000', 'current_quantity' => '50.000']);

        $this->dispatch([$this->batch->id => '10', $second->id => '20']);
        $this->actingAs($this->staff)->post(route('shipments.receipt.store', Shipment::sole()));

        $this->assertSame('10.000', $this->balance($this->batch, $this->warehouse));
        $this->assertSame('20.000', $this->balance($second, $this->warehouse));
        $this->assertLedgerConsistent($this->batch);
        $this->assertLedgerConsistent($second);
    }

    public function test_the_full_chain_of_custody_is_visible_on_the_batch_page(): void
    {
        $shop = Location::factory()->create(['name' => 'Musanze shop']);

        $this->dispatch([$this->batch->id => '40']);
        $this->actingAs($this->staff)->post(route('shipments.receipt.store', Shipment::sole()));

        $this->actingAs($this->staff)->post(route('shipments.store'), [
            'from_location_id' => $this->warehouse->id,
            'to_location_id' => $shop->id,
            'items' => [$this->batch->id => ['quantity' => '15']],
        ]);
        $onward = Shipment::latest('id')->first();

        $this->actingAs($this->staff)
            ->get(route('batches.show', $this->batch))
            ->assertOk()
            ->assertSeeInOrder(['Where the stock is now', 'Masoro plant', 'Kigali warehouse', 'In transit', $onward->reference])
            ->assertSee('Produced at')
            ->assertSee('Dispatched from')
            ->assertSee('Received at');

        $this->assertLedgerConsistent($this->batch);
    }

    public function test_shipments_can_be_found_by_reference_and_status(): void
    {
        // Via the actions, so no "SHP-..." flash message leaks into the pages below.
        $dispatch = app(DispatchShipment::class);
        $first = $dispatch->handle($this->plant, $this->warehouse, [$this->batch->id => '1'], null, $this->staff);
        $second = $dispatch->handle($this->plant, $this->warehouse, [$this->batch->id => '1'], null, $this->staff);
        app(ReceiveShipment::class)->handle($second, $this->staff);

        $this->actingAs($this->staff)
            ->get(route('shipments.index', ['q' => $first->reference]))
            ->assertSee($first->reference)
            ->assertDontSee($second->reference);

        $this->actingAs($this->staff)
            ->get(route('shipments.index', ['status' => 'received']))
            ->assertSee($second->reference)
            ->assertDontSee($first->reference);
    }

    public function test_references_are_derived_from_the_id(): void
    {
        $this->assertSame('SHP-000042', Shipment::formatReference(42));
        $this->assertSame(42, Shipment::idFromReference('SHP-000042'));
        $this->assertSame(42, Shipment::idFromReference('shp-42'));
        $this->assertSame(42, Shipment::idFromReference('42'));
        $this->assertNull(Shipment::idFromReference('not a reference'));
    }
}
