<?php

namespace Tests\Feature;

use App\Enums\ProductCategory;
use App\Enums\UnitOfMeasure;
use App\Models\Batch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create();
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'product_code' => 'RW-MAIZE-25KG',
            'name' => 'Maize flour 25 kg',
            'description' => 'Fortified maize flour.',
            'category' => ProductCategory::FoodAndBeverages->value,
            'manufacturer_name' => 'Kigali Mills Ltd',
            'unit_of_measure' => UnitOfMeasure::Bag->value,
        ], $overrides);
    }

    public function test_the_product_list_shows_an_empty_state(): void
    {
        $this->actingAs($this->staff)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('No products yet');
    }

    public function test_the_product_list_is_paginated(): void
    {
        Product::factory(20)->create();

        $response = $this->actingAs($this->staff)->get(route('products.index'));

        $response->assertOk();
        $this->assertCount(12, $response->viewData('products'));
        $this->assertSame(20, $response->viewData('products')->total());
    }

    public function test_a_valid_product_can_be_created(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('products.store'), $this->validPayload(['product_code' => 'rw-maize-25kg']));

        $product = Product::sole();

        $response->assertRedirect(route('products.show', $product))
            ->assertSessionHas('success');

        // Codes are normalised to upper case; new products start active.
        $this->assertSame('RW-MAIZE-25KG', $product->product_code);
        $this->assertSame(ProductCategory::FoodAndBeverages, $product->category);
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_duplicate_product_codes_are_rejected_regardless_of_case(): void
    {
        Product::factory()->create(['product_code' => 'RW-MAIZE-25KG']);

        $this->actingAs($this->staff)
            ->post(route('products.store'), $this->validPayload(['product_code' => 'rw-maize-25kg']))
            ->assertSessionHasErrors(['product_code' => 'A product with this code already exists.']);

        $this->assertSame(1, Product::count());
    }

    public function test_invalid_product_data_is_rejected(): void
    {
        $this->actingAs($this->staff)
            ->post(route('products.store'), [
                'product_code' => 'BAD CODE!',
                'name' => '',
                'category' => 'not-a-category',
                'manufacturer_name' => str_repeat('x', 151),
                'unit_of_measure' => 'furlongs',
            ])
            ->assertSessionHasErrors(['product_code', 'name', 'category', 'manufacturer_name', 'unit_of_measure']);

        $this->assertSame(0, Product::count());
    }

    public function test_users_cannot_set_the_active_flag_through_the_create_form(): void
    {
        $this->actingAs($this->staff)
            ->post(route('products.store'), $this->validPayload(['is_active' => '0']));

        $this->assertTrue(Product::sole()->is_active);
    }

    public function test_products_can_be_searched_by_name_or_code(): void
    {
        Product::factory()->create(['name' => 'Inyange milk 500 ml', 'product_code' => 'MILK-500']);
        Product::factory()->create(['name' => 'Cassava flour', 'product_code' => 'CSV-001']);

        $this->actingAs($this->staff)
            ->get(route('products.index', ['q' => 'inyange']))
            ->assertSee('Inyange milk 500 ml')
            ->assertDontSee('Cassava flour');

        $this->actingAs($this->staff)
            ->get(route('products.index', ['q' => 'csv-0']))
            ->assertSee('Cassava flour')
            ->assertDontSee('Inyange milk 500 ml');
    }

    public function test_products_can_be_filtered_by_category_and_status(): void
    {
        Product::factory()->create(['name' => 'Paracetamol tablets', 'category' => ProductCategory::Pharmaceuticals]);
        Product::factory()->create(['name' => 'Arabica coffee beans', 'category' => ProductCategory::Agriculture]);
        Product::factory()->inactive()->create(['name' => 'Discontinued syrup', 'category' => ProductCategory::Pharmaceuticals]);

        $this->actingAs($this->staff)
            ->get(route('products.index', ['category' => 'pharmaceuticals']))
            ->assertSee('Paracetamol tablets')
            ->assertSee('Discontinued syrup')
            ->assertDontSee('Arabica coffee beans');

        $this->actingAs($this->staff)
            ->get(route('products.index', ['category' => 'pharmaceuticals', 'status' => 'inactive']))
            ->assertSee('Discontinued syrup')
            ->assertDontSee('Paracetamol tablets');
    }

    public function test_unknown_filter_values_are_ignored(): void
    {
        Product::factory()->create(['name' => 'Arabica coffee beans']);

        $this->actingAs($this->staff)
            ->get(route('products.index', ['category' => 'nonsense', 'status' => 'deleted']))
            ->assertOk()
            ->assertSee('Arabica coffee beans');
    }

    public function test_the_product_page_lists_its_batches(): void
    {
        $product = Product::factory()->create();
        $batch = Batch::factory()->for($product)->create();
        $otherBatch = Batch::factory()->create();

        $this->actingAs($this->staff)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->product_code)
            ->assertSee($batch->batch_number)
            ->assertDontSee($otherBatch->batch_number);
    }

    public function test_a_product_can_be_updated(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->staff)
            ->put(route('products.update', $product), $this->validPayload(['name' => 'Renamed product']))
            ->assertRedirect(route('products.show', $product));

        $this->assertSame('Renamed product', $product->fresh()->name);
    }

    public function test_a_product_can_keep_its_own_code_when_updated(): void
    {
        $product = Product::factory()->create(['product_code' => 'RW-MAIZE-25KG']);

        $this->actingAs($this->staff)
            ->put(route('products.update', $product), $this->validPayload())
            ->assertSessionHasNoErrors();
    }

    public function test_the_product_code_cannot_change_once_batches_exist(): void
    {
        $product = Product::factory()->create(['product_code' => 'ORIGINAL-CODE']);
        Batch::factory()->for($product)->create();

        $this->actingAs($this->staff)
            ->put(route('products.update', $product), $this->validPayload(['product_code' => 'NEW-CODE']))
            ->assertSessionHasErrors('product_code');

        $this->assertSame('ORIGINAL-CODE', $product->fresh()->product_code);
    }

    public function test_staff_cannot_change_a_products_status(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->staff)
            ->patch(route('products.status', $product), ['is_active' => '0'])
            ->assertForbidden();

        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_administrators_can_deactivate_and_reactivate_a_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->patch(route('products.status', $product), ['is_active' => '0'])
            ->assertRedirect(route('products.show', $product));
        $this->assertFalse($product->fresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('products.status', $product), ['is_active' => '1']);
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_products_cannot_be_deleted_through_the_application(): void
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete("/products/{$product->id}")
            ->assertMethodNotAllowed();

        $this->assertModelExists($product);
    }
}
