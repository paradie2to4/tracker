<?php

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Location;
use App\Models\Product;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_and_updating_a_product_is_audited_with_the_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['name' => 'Original name']);
        $product->update(['name' => 'New name']);

        $created = AuditLog::where('event', 'product.created')->sole();
        $this->assertSame('product', $created->subject_type);
        $this->assertSame($product->id, $created->subject_id);
        $this->assertSame($user->id, $created->user_id);

        $updated = AuditLog::where('event', 'product.updated')->sole();
        $this->assertSame(['name' => 'Original name'], $updated->old_values);
        $this->assertSame(['name' => 'New name'], $updated->new_values);
    }

    public function test_domain_events_are_audited(): void
    {
        $admin = User::factory()->admin()->create();
        $batch = Batch::factory()->create();
        $destination = Location::factory()->create();

        $this->actingAs($admin)->post(route('shipments.store'), [
            'from_location_id' => $batch->origin_location_id,
            'to_location_id' => $destination->id,
            'items' => [$batch->id => ['quantity' => '1']],
        ]);
        $this->actingAs($admin)->post(route('shipments.receipt.store', Shipment::sole()));
        $this->actingAs($admin)->post(route('batches.recall', $batch), ['recall_reason' => 'Contamination found in testing.']);

        foreach (['shipment.dispatched', 'shipment.received', 'batch.recalled'] as $event) {
            $this->assertSame(1, AuditLog::where('event', $event)->where('user_id', $admin->id)->count(), "Missing audit event {$event}");
        }
    }

    public function test_only_administrators_can_view_the_audit_log(): void
    {
        Product::factory()->create(['name' => 'Audited product']);

        $this->actingAs(User::factory()->create())->get(route('audit.index'))->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('product.created')
            ->assertSee('Audited product');
    }

    public function test_the_audit_log_reads_as_plain_sentences_without_internal_fields(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Aline Uwase']);
        $this->actingAs($admin);

        $location = Location::factory()->create(['name' => 'Gikondo depot', 'is_active' => true]);
        $location->update(['name' => 'Gikondo warehouse']);

        $this->get(route('audit.index'))
            ->assertOk()
            ->assertSeeInOrder(['Aline Uwase', 'updated location', 'Gikondo warehouse'])
            ->assertSee(route('locations.show', $location), false)
            ->assertSeeInOrder(['Name', 'Gikondo depot', 'Gikondo warehouse'])
            ->assertSee('Yes')
            ->assertDontSee('organization_id')
            ->assertDontSee('is_active');
    }

    public function test_audit_entries_and_stock_movements_cannot_be_changed_through_the_application(): void
    {
        $batch = Batch::factory()->create();
        $entry = AuditLog::where('event', 'batch.created')->firstOrFail();
        $movement = StockMovement::where('batch_id', $batch->id)->firstOrFail();

        foreach ([
            fn () => $entry->update(['event' => 'tampered']),
            fn () => $entry->delete(),
            // forceFill bypasses mass-assignment protection, so this reaches
            // the append-only guard itself.
            fn () => $movement->forceFill(['quantity' => '1'])->save(),
            fn () => $movement->delete(),
        ] as $attempt) {
            try {
                $attempt();
                $this->fail('An append-only record was modified.');
            } catch (LogicException) {
                // expected
            }
        }

        $this->assertSame('batch.created', $entry->fresh()->event);
        $this->assertSame(MovementType::Production, $movement->fresh()->type);
    }

    public function test_the_database_rejects_changes_to_append_only_tables(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Append-only triggers are created on PostgreSQL only.');
        }

        Batch::factory()->create();

        foreach (['stock_movements', 'audit_logs'] as $table) {
            foreach (['update', 'delete'] as $operation) {
                try {
                    DB::transaction(fn () => $operation === 'update'
                        ? DB::table($table)->update(['created_at' => now()])
                        : DB::table($table)->delete());
                    $this->fail("The database allowed {$operation} on {$table}.");
                } catch (QueryException $exception) {
                    $this->assertStringContainsString('append-only', $exception->getMessage());
                }
            }
        }
    }
}
