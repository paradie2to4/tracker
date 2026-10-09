<?php

namespace Database\Factories;

use App\Enums\LocationType;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $district = fake()->randomElement(Arr::flatten(config('productsphere.districts')));

        return [
            'organization_id' => Organization::factory(),
            'code' => strtoupper(fake()->unique()->bothify('LOC-###??')),
            'name' => $district.' '.fake()->randomElement(['Warehouse', 'Depot', 'Plant', 'Store']),
            'type' => fake()->randomElement(LocationType::cases()),
            'district' => $district,
            'address' => null,
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
