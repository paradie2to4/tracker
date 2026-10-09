<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BatchManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Product $product;

    private Location $plant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create();
        $this->product = Product::factory()->create();
        $this->plant = Location::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'product_id' => $this->product->id,
            'origin_location_id' => $this->plant->id,
            'batch_number' => 'LOT-2026-0001',
            'manufacturing_date' => today()->subDays(10)->toDateString(),
            'expiry_date' => today()->addYear()->toDateString(),
            'initial_quantity' => '1250.500',
        ], $overrides);
    }

    public function test_the_batch_list_shows_an_empty_state(): void
    {
        $this->actingAs($this->staff)
            ->get(route('batches.index'))
            ->assertOk()
            ->assertSee('No batches yet');
    }

    public function test_a_valid_batch_can_be_created_for_an_existing_product(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['batch_number' => 'lot-2026-0001']));

        $batch = Batch::sole();

        $response->assertRedirect(route('batches.show', $batch))->assertSessionHas('success');

        $this->assertSame($this->product->id, $batch->product_id);
        $this->assertSame('LOT-2026-0001', $batch->batch_number);
        $this->assertSame('1250.500', $batch->initial_quantity);
        // A new batch starts with all of its stock available...
        $this->assertSame('1250.500', $batch->current_quantity);

        // ...placed at the production location, explained by a ledger entry.
        $balance = StockBalance::sole();
        $this->assertSame($this->plant->id, $balance->location_id);
        $this->assertSame('1250.500', $balance->quantity);

        $movement = StockMovement::sole();
        $this->assertSame(MovementType::Production, $movement->type);
        $this->assertSame($this->plant->id, $movement->to_location_id);
        $this->assertSame('1250.500', $movement->quantity);
        $this->assertSame($this->staff->id, $movement->user_id);
    }

    public function test_a_production_location_is_required_and_must_be_active(): void
    {
        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['origin_location_id' => '']))
            ->assertSessionHasErrors('origin_location_id');

        $closed = Location::factory()->inactive()->create();

        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['origin_location_id' => $closed->id]))
            ->assertSessionHasErrors(['origin_location_id' => 'Select an active production location.']);

        $this->assertSame(0, Batch::count());
        $this->assertSame(0, StockMovement::count());
    }

    public function test_the_expiry_date_is_optional(): void
    {
        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['expiry_date' => '']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Batch::sole()->expiry_date);
    }

    public function test_duplicate_batch_numbers_are_rejected(): void
    {
        Batch::factory()->create(['batch_number' => 'LOT-2026-0001']);

        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['batch_number' => 'lot-2026-0001']))
            ->assertSessionHasErrors(['batch_number' => 'A batch with this number already exists.']);

        $this->assertSame(1, Batch::count());
    }

    public function test_an_expiry_date_before_the_manufacturing_date_is_rejected(): void
    {
        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload([
                'manufacturing_date' => today()->subDays(5)->toDateString(),
                'expiry_date' => today()->subDays(6)->toDateString(),
            ]))
            ->assertSessionHasErrors(['expiry_date' => 'The expiry date cannot be earlier than the manufacturing date.']);

        $this->assertSame(0, Batch::count());
    }

    public function test_an_expiry_date_equal_to_the_manufacturing_date_is_accepted(): void
    {
        $date = today()->subDay()->toDateString();

        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['manufacturing_date' => $date, 'expiry_date' => $date]))
            ->assertSessionHasNoErrors();
    }

    public function test_a_future_manufacturing_date_is_rejected(): void
    {
        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['manufacturing_date' => today()->addDay()->toDateString()]))
            ->assertSessionHasErrors(['manufacturing_date' => 'The manufacturing date cannot be in the future.']);
    }

    public function test_negative_and_zero_quantities_are_rejected(): void
    {
        foreach (['-5', '0', '0.000'] as $quantity) {
            $this->actingAs($this->staff)
                ->post(route('batches.store'), $this->validPayload(['initial_quantity' => $quantity]))
                ->assertSessionHasErrors('initial_quantity');
        }

        $this->assertSame(0, Batch::count());
    }

    public function test_quantities_with_more_than_three_decimal_places_are_rejected(): void
    {
        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['initial_quantity' => '10.1234']))
            ->assertSessionHasErrors('initial_quantity');
    }

    public function test_batches_cannot_be_created_for_inactive_or_missing_products(): void
    {
        $inactive = Product::factory()->inactive()->create();

        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['product_id' => $inactive->id]))
            ->assertSessionHasErrors(['product_id' => 'Select an active product.']);

        $this->actingAs($this->staff)
            ->post(route('batches.store'), $this->validPayload(['product_id' => 999999]))
            ->assertSessionHasErrors('product_id');

        $this->assertSame(0, Batch::count());
    }

    public function test_the_dates_of_a_batch_can_be_corrected(): void
    {
        $batch = Batch::factory()->for($this->product)->create();
        $newDate = today()->subDays(3)->toDateString();

        $this->actingAs($this->staff)
            ->put(route('batches.update', $batch), [
                'manufacturing_date' => $newDate,
                'expiry_date' => '',
            ])
            ->assertRedirect(route('batches.show', $batch));

        $batch->refresh();
        $this->assertSame($newDate, $batch->manufacturing_date->toDateString());
        $this->assertNull($batch->expiry_date);
    }

    public function test_quantities_cannot_be_edited_directly_once_stock_is_tracked(): void
    {
        $batch = Batch::factory()->create(['initial_quantity' => '100.000', 'current_quantity' => '100.000']);

        $this->actingAs($this->staff)
            ->put(route('batches.update', $batch), [
                'manufacturing_date' => $batch->manufacturing_date->toDateString(),
                'initial_quantity' => '500',
                'current_quantity' => '1',
            ])
            ->assertSessionHasNoErrors();

        // The submitted quantities are ignored: only movements change stock.
        $batch->refresh();
        $this->assertSame('100.000', $batch->initial_quantity);
        $this->assertSame('100.000', $batch->current_quantity);
        $this->assertSame('100.000', StockBalance::where('batch_id', $batch->id)->sole()->quantity);
    }

    public function test_the_product_and_batch_number_cannot_be_changed(): void
    {
        $batch = Batch::factory()->for($this->product)->create(['batch_number' => 'ORIGINAL-LOT']);
        $otherProduct = Product::factory()->create();

        $this->actingAs($this->staff)
            ->put(route('batches.update', $batch), [
                'product_id' => $otherProduct->id,
                'batch_number' => 'CHANGED-LOT',
                'manufacturing_date' => $batch->manufacturing_date->toDateString(),
                'initial_quantity' => $batch->initial_quantity,
                'current_quantity' => $batch->current_quantity,
            ])
            ->assertSessionHasNoErrors();

        $batch->refresh();
        $this->assertSame('ORIGINAL-LOT', $batch->batch_number);
        $this->assertSame($this->product->id, $batch->product_id);
    }

    public function test_batches_can_be_searched_and_filtered(): void
    {
        $otherProduct = Product::factory()->create();
        Batch::factory()->for($this->product)->create(['batch_number' => 'MAIZE-001']);
        Batch::factory()->for($this->product)->expired()->create(['batch_number' => 'MAIZE-002']);
        Batch::factory()->for($otherProduct)->create(['batch_number' => 'MILK-001']);

        $this->actingAs($this->staff)
            ->get(route('batches.index', ['q' => 'maize']))
            ->assertSee('MAIZE-001')->assertSee('MAIZE-002')->assertDontSee('MILK-001');

        $this->actingAs($this->staff)
            ->get(route('batches.index', ['product' => $otherProduct->id]))
            ->assertSee('MILK-001')->assertDontSee('MAIZE-001');

        $this->actingAs($this->staff)
            ->get(route('batches.index', ['status' => 'expired']))
            ->assertSee('MAIZE-002')->assertDontSee('MAIZE-001')->assertDontSee('MILK-001');
    }

    public function test_the_batch_details_page_can_be_viewed(): void
    {
        $batch = Batch::factory()->for($this->product)->create();

        $this->actingAs($this->staff)
            ->get(route('batches.show', $batch))
            ->assertOk()
            ->assertSee($batch->batch_number)
            ->assertSee($this->product->name);
    }

    public function test_staff_cannot_recall_a_batch(): void
    {
        $batch = Batch::factory()->create();

        $this->actingAs($this->staff)
            ->post(route('batches.recall', $batch), ['recall_reason' => 'Suspected contamination in the line.'])
            ->assertForbidden();

        $this->assertNull($batch->fresh()->recalled_at);
    }

    public function test_administrators_can_recall_a_batch_with_a_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $batch = Batch::factory()->create();

        $this->actingAs($admin)
            ->post(route('batches.recall', $batch), ['recall_reason' => 'short'])
            ->assertSessionHasErrors('recall_reason');

        $this->actingAs($admin)
            ->post(route('batches.recall', $batch), ['recall_reason' => 'Suspected contamination in the line.'])
            ->assertRedirect(route('batches.show', $batch))
            ->assertSessionHas('success');

        $batch->refresh();
        $this->assertNotNull($batch->recalled_at);
        $this->assertSame($admin->id, $batch->recalled_by);
        $this->assertSame('Suspected contamination in the line.', $batch->recall_reason);
    }

    public function test_a_recalled_batch_cannot_be_edited_or_recalled_again(): void
    {
        $admin = User::factory()->admin()->create();
        $batch = Batch::factory()->recalled('Original recall reason.')->create();

        $this->actingAs($admin)->get(route('batches.edit', $batch))->assertForbidden();
        $this->actingAs($admin)
            ->put(route('batches.update', $batch), [
                'manufacturing_date' => $batch->manufacturing_date->toDateString(),
                'initial_quantity' => '1',
                'current_quantity' => '1',
            ])
            ->assertForbidden();
        $this->actingAs($admin)
            ->post(route('batches.recall', $batch), ['recall_reason' => 'A second, different recall reason.'])
            ->assertForbidden();

        $this->assertSame('Original recall reason.', $batch->fresh()->recall_reason);
    }

    public function test_the_database_rejects_invalid_quantities_and_dates_directly(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('CHECK constraints are created on PostgreSQL only.');
        }

        $base = [
            'product_id' => $this->product->id,
            'manufacturing_date' => '2026-01-10',
            'initial_quantity' => 10,
            'current_quantity' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $invalidRows = [
            ['batch_number' => 'DB-NEG', 'current_quantity' => -1],
            ['batch_number' => 'DB-OVER', 'current_quantity' => 11],
            ['batch_number' => 'DB-ZERO', 'initial_quantity' => 0, 'current_quantity' => 0],
            ['batch_number' => 'DB-DATE', 'expiry_date' => '2026-01-09'],
        ];

        foreach ($invalidRows as $row) {
            try {
                DB::transaction(fn () => DB::table('batches')->insert([...$base, ...$row]));
                $this->fail("The database accepted an invalid row: {$row['batch_number']}");
            } catch (QueryException $exception) {
                $this->assertStringContainsString('check constraint', $exception->getMessage());
            }
        }

        $this->assertSame(0, Batch::count());
    }

    public function test_a_product_with_batches_cannot_be_deleted_at_the_database_level(): void
    {
        $batch = Batch::factory()->for($this->product)->create();

        $this->expectException(QueryException::class);

        try {
            $this->product->delete();
        } finally {
            $this->assertModelExists($batch);
        }
    }
}
