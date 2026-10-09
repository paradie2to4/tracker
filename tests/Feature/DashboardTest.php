<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_access_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Registered products');
    }

    public function test_the_dashboard_shows_zero_values_for_an_empty_database(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('productCount', 0)
            ->assertViewHas('batchCount', 0)
            ->assertViewHas('expiredCount', 0)
            ->assertViewHas('expiringSoonCount', 0)
            ->assertSee('No batches are approaching expiry');
    }

    public function test_the_dashboard_statistics_are_calculated_from_real_records(): void
    {
        $product = Product::factory()->create();
        Product::factory()->inactive()->create();

        Batch::factory()->for($product)->create();                          // active, far from expiry
        Batch::factory()->for($product)->expired()->create();               // expired
        Batch::factory()->for($product)->expired()->create();               // expired
        Batch::factory()->for($product)->expiringSoon(5)->create();         // approaching expiry
        Batch::factory()->for($product)->expiringSoon(0)->create();         // expires today: still approaching
        Batch::factory()->for($product)->expiringSoon(31)->create();        // outside the 30-day window
        Batch::factory()->for($product)->expired()->recalled()->create();   // recalled wins over expired
        Batch::factory()->for($product)->expired()->depleted()->create();   // depleted wins over expired
        Batch::factory()->for($product)->expiringSoon(3)->depleted()->create(); // no stock, no warning

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('productCount', 2)
            ->assertViewHas('activeProductCount', 1)
            ->assertViewHas('batchCount', 9)
            ->assertViewHas('expiredCount', 2)
            ->assertViewHas('expiringSoonCount', 2)
            ->assertViewHas('recalledCount', 1);
    }

    public function test_the_expiry_warning_window_is_configurable(): void
    {
        config(['productsphere.expiry_warning_days' => 7]);

        Batch::factory()->expiringSoon(5)->create();
        Batch::factory()->expiringSoon(10)->create();

        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertViewHas('expiringSoonCount', 1);
    }
}
