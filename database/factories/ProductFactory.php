<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'comment' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1, 100),
            'category_id' => rand(1, 10),
            'user_id' => 1,
        ];
    }
}
