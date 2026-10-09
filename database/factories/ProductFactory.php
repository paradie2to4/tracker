<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Enums\UnitOfMeasure;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * Realistic sample products: [name, category, unit, description].
     *
     * @var list<array{0: string, 1: ProductCategory, 2: UnitOfMeasure, 3: string|null}>
     */
    private const SAMPLES = [
        ['Maize flour', ProductCategory::FoodAndBeverages, UnitOfMeasure::Bag, 'Fortified maize flour in 25 kg bags.'],
        ['Long-life milk 500 ml', ProductCategory::FoodAndBeverages, UnitOfMeasure::Carton, 'UHT milk, 12 packs per carton.'],
        ['Mineral water 1.5 l', ProductCategory::FoodAndBeverages, UnitOfMeasure::Bottle, null],
        ['Pineapple juice 1 l', ProductCategory::FoodAndBeverages, UnitOfMeasure::Bottle, 'No added sugar.'],
        ['Fully washed Arabica coffee', ProductCategory::Agriculture, UnitOfMeasure::Kilogram, 'Green beans, export grade.'],
        ['Black tea', ProductCategory::Agriculture, UnitOfMeasure::Kilogram, 'CTC black tea for blending.'],
        ['Irish potatoes', ProductCategory::Agriculture, UnitOfMeasure::Tonne, null],
        ['Dried beans', ProductCategory::Agriculture, UnitOfMeasure::Bag, null],
        ['Paracetamol 500 mg tablets', ProductCategory::Pharmaceuticals, UnitOfMeasure::Box, '100 tablets per box.'],
        ['Oral rehydration salts', ProductCategory::Pharmaceuticals, UnitOfMeasure::Box, null],
        ['Hand sanitiser 500 ml', ProductCategory::Chemicals, UnitOfMeasure::Bottle, '70% alcohol.'],
        ['Body lotion 400 ml', ProductCategory::Cosmetics, UnitOfMeasure::Bottle, null],
        ['Bar soap', ProductCategory::Cosmetics, UnitOfMeasure::Carton, '48 bars per carton.'],
        ['Cotton fabric', ProductCategory::Textiles, UnitOfMeasure::Piece, 'Bolts of 50 m.'],
        ['Portland cement 50 kg', ProductCategory::ConstructionMaterials, UnitOfMeasure::Bag, 'CEM II 42.5 N.'],
        ['Solar lantern', ProductCategory::Electronics, UnitOfMeasure::Piece, null],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$name, $category, $unit, $description] = fake()->randomElement(self::SAMPLES);

        return [
            // Upper case, as the Form Request would normalise it.
            'product_code' => strtoupper(fake()->unique()->bothify('PRD-####-??')),
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'manufacturer_name' => fake()->company(),
            'unit_of_measure' => $unit,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
