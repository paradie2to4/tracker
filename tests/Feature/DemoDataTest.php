<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Enums\ShipmentStatus;
use App\Models\Batch;
use App\Models\Organization;
use App\Models\Shipment;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_demo_supply_chain_can_be_loaded(): void
    {
        $this->artisan('app:seed-demo')->assertSuccessful();

        $this->assertSame(8, Organization::count());
        $this->assertSame(13, Batch::count());
        $this->assertSame(2, Shipment::where('status', ShipmentStatus::InTransit)->count());
        $this->assertSame(1, Shipment::where('status', ShipmentStatus::Cancelled)->count());

        // Every status appears, so the dashboard and filters have something to show.
        foreach (BatchStatus::cases() as $status) {
            $this->assertTrue(Batch::withStatus($status)->exists(), "No demo batch is {$status->value}.");
        }

        $this->assertTrue(Batch::expiringWithin(30)->exists(), 'No demo batch is approaching expiry.');

        // The clock is restored after seeding.
        $this->assertFalse(Carbon::hasTestNow());
    }

    public function test_the_demo_history_is_spread_over_time(): void
    {
        $this->artisan('app:seed-demo');

        $oldest = StockMovement::min('occurred_at');
        $this->assertTrue(Carbon::parse($oldest)->lt(now()->subDays(100)));
    }

    public function test_the_demo_ledger_is_consistent(): void
    {
        $this->artisan('app:seed-demo');

        foreach (Batch::all() as $batch) {
            $atLocations = StockBalance::where('batch_id', $batch->id)->get()
                ->reduce(fn (BigDecimal $carry, StockBalance $b) => $carry->plus($b->quantity), BigDecimal::zero());

            $inTransit = Shipment::where('status', ShipmentStatus::InTransit)
                ->with(['items' => fn ($query) => $query->where('batch_id', $batch->id)])
                ->get()->flatMap->items
                ->reduce(fn (BigDecimal $carry, $item) => $carry->plus($item->quantity), BigDecimal::zero());

            $this->assertTrue(
                $atLocations->plus($inTransit)->isEqualTo($batch->current_quantity),
                "Batch {$batch->batch_number}: {$atLocations} + {$inTransit} != {$batch->current_quantity}",
            );
        }
    }

    public function test_seeding_twice_does_not_duplicate_anything(): void
    {
        $this->artisan('app:seed-demo');
        $this->artisan('app:seed-demo')->expectsOutputToContain('already present')->assertSuccessful();

        $this->assertSame(13, Batch::count());
    }

    public function test_no_demo_account_has_a_guessable_password(): void
    {
        $this->artisan('app:seed-demo');

        foreach (User::all() as $user) {
            foreach (['password', 'password123', 'demo', 'admin'] as $guess) {
                $this->assertFalse(Hash::check($guess, $user->password), "{$user->email} uses a guessable password.");
            }
        }
    }
}
