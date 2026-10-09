<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use App\Support\PageSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->admin()->create();
    }

    public function test_lists_show_fewer_records_on_phones(): void
    {
        Product::factory(15)->create();

        $desktop = $this->actingAs($this->user)->get(route('products.index'));
        $this->assertCount(PageSize::LIST[0], $desktop->viewData('products'));

        $phone = $this->actingAs($this->user)
            ->withUnencryptedCookie(PageSize::COOKIE, '1')
            ->get(route('products.index'));
        $this->assertCount(PageSize::LIST[1], $phone->viewData('products'));
    }

    public function test_nested_lists_and_the_audit_log_use_their_own_sizes(): void
    {
        $product = Product::factory()->create();
        Batch::factory(10)->for($product)->create();

        $this->assertCount(PageSize::NESTED[0], $this->actingAs($this->user)->get(route('products.show', $product))->viewData('batches'));
        $this->assertCount(PageSize::NESTED[1], $this->actingAs($this->user)->withUnencryptedCookie(PageSize::COOKIE, '1')->get(route('products.show', $product))->viewData('batches'));

        $this->assertGreaterThan(PageSize::FEED[0], AuditLog::count());
        $this->assertCount(PageSize::FEED[0], $this->actingAs($this->user)->withUnencryptedCookie(PageSize::COOKIE, '0')->get(route('audit.index'))->viewData('logs'));
    }

    public function test_the_pagination_controls_are_themed_for_both_screen_sizes(): void
    {
        Product::factory(30)->create();

        $this->actingAs($this->user)
            ->get(route('products.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Page <span class="font-medium text-ink-900">2</span> of 3', false)
            ->assertSee('Showing <span class="font-medium text-ink-900">13–24</span>', false)
            ->assertSee('aria-current="page"', false)
            ->assertSee('rel="prev"', false)
            ->assertSee('rel="next"', false);
    }

    public function test_filters_are_kept_when_changing_page(): void
    {
        Product::factory(20)->create(['category' => 'pharmaceuticals']);

        $this->actingAs($this->user)
            ->get(route('products.index', ['category' => 'pharmaceuticals']))
            ->assertSee('category=pharmaceuticals&amp;page=2', false);
    }
}
