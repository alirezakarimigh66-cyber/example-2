<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10000, 5000000),
            'stock_quantity' => fake()->numberBetween(0, 100),
            'status' => ProductStatus::Inactive,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['status' => ProductStatus::Active]);
    }
}
