<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderStatus>
 */
class OrderStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusNames = ['Processing', 'Shipped', 'Delivered'];
        
        return [
            'name' => fake()->unique()->randomElement($statusNames),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }
}
