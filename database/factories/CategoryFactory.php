<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Electronics',
                'Clothing',
                'Food & Beverages',
                'Home & Garden',
                'Sports & Outdoors',
                'Automotive',
                'Health & Beauty',
                'Toys & Games',
                'Books',
                'Office Supplies',
            ]),
            'description' => fake()->sentence(),
        ];
    }
}
