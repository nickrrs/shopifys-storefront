<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'shopify_product_id' => 'gid://shopify/Product/'.fake()->unique()->numerify('##########'),
            'title' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 1, 999),
            'inventory_quantity' => fake()->optional()->numberBetween(0, 500),
            'status' => fake()->randomElement(['ACTIVE', 'DRAFT', 'ARCHIVED']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'ACTIVE']);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'DRAFT']);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'ARCHIVED']);
    }
}
