<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        $categories = ['Electronics', 'Clothing', 'Food & Beverages', 'Home & Garden', 'Automotive'];
        
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 500),
            'stock' => fake()->numberBetween(0, 100),
            'image_url' => fake()->imageUrl(300, 300),
            'category' => fake()->randomElement($categories),
        ];
    }
}
