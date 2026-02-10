<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Store> */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'shopify_domain' => fake()->unique()->domainWord().'.myshopify.com',
            'access_token' => 'shpat_'.fake()->sha256(),
            'connected_at' => now(),
            'syncing' => false,
        ];
    }

    public function syncing(): static
    {
        return $this->state(fn () => ['syncing' => true]);
    }
}
