<?php

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class ShopAiSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [];
        $categoryNames = [
            'Electronics',
            'Clothing',
            'Food & Beverages',
            'Home & Garden',
            'Automotive Parts',
        ];

        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'name' => $name,
                'description' => "Products related to {$name}",
            ]);
        }

        // Create 20 products
        $productData = [
            ['Smartphone X', 'Electronics', 699.99, 50, 10, 'SMX-001', 'Samsung', 'Galaxy Series'],
            ['Laptop Pro 15', 'Electronics', 1299.99, 25, 5, 'LPP-015', 'Dell', 'XPS'],
            ['Brake Pad Set', 'Automotive Parts', 49.99, 100, 20, 'BP-101', 'Bosch', 'Honda Civic'],
            ['Oil Filter', 'Automotive Parts', 19.99, 150, 30, 'OF-205', 'Mann', 'Toyota Camry'],
            ['Cotton T-Shirt', 'Clothing', 19.99, 200, 40, 'CT-001', 'Nike', 'M/Black'],
            ['Coffee Beans 1kg', 'Food & Beverages', 24.99, 80, 15, 'CB-100', 'Arabica', 'Premium'],
            ['LED Table Lamp', 'Home & Garden', 39.99, 45, 10, 'LT-050', 'Philips', 'Standard'],
            ['Spark Plug Set', 'Automotive Parts', 34.99, 60, 15, 'SP-401', 'NGK', 'Iridium'],
            ['Running Sneakers', 'Clothing', 89.99, 35, 8, 'RS-200', 'Adidas', 'Size 42'],
            ['Organic Tea Leaves', 'Food & Beverages', 12.99, 120, 25, 'TL-010', 'Twinings', 'Green Tea'],
            ['Garden Plant Pot', 'Home & Garden', 15.99, 75, 15, 'GP-300', 'Eco', '12 inch'],
            ['Air Filter Cabin', 'Automotive Parts', 29.99, 90, 20, 'AF-C11', 'Denso', 'Premium'],
            ['Wireless Earbuds', 'Electronics', 129.99, 40, 10, 'WEB-500', 'Apple', 'AirPods Pro'],
            ['Denim Jeans', 'Clothing', 59.99, 55, 12, 'DJ-350', 'Levi\'s', 'Slim Fit'],
            ['Energy Drink Pack', 'Food & Beverages', 29.99, 100, 25, 'ED-006', 'Red Bull', '12-Pack'],
            ['Office Chair', 'Home & Garden', 199.99, 15, 3, 'OC-120', 'IKEA', 'Ergonomic'],
            ['Car Battery 12V', 'Automotive Parts', 149.99, 20, 5, 'CB-601', 'Exide', '60Ah'],
            ['Winter Jacket', 'Clothing', 129.99, 30, 8, 'WJ-450', 'The North Face', 'Large'],
            ['Protein Bar Box', 'Food & Beverages', 34.99, 85, 20, 'PB-210', 'PowerBar', '12-Count'],
            ['Smart Watch', 'Electronics', 249.99, 28, 6, 'SW-900', 'Garmin', 'GPS'],
        ];

        foreach ($productData as $data) {
            $category = Category::where('name', $data[1])->first();
            if ($category) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $data[0],
                    'description' => fake()->sentence(),
                    'price' => $data[2],
                    'stock_quantity' => $data[3],
                    'min_stock_level' => $data[4],
                    'sku' => $data[5],
                    'image_url' => fake()->imageUrl(300, 300),
                    'brand' => $data[6],
                    'part_number' => $data[7] ?? 'PN-' . strtoupper(fake()->bothify('###')),
                    'vehicle_model' => in_array($data[1], ['Automotive Parts']) ? fake()->randomElement(['Sedan', 'SUV', 'Truck']) : null,
                    'compatibility' => in_array($data[1], ['Automotive Parts']) ? fake()->randomElement(['Honda', 'Toyota', 'Ford', 'GM']) : null,
                ]);
            }
        }

        echo "Seeded " . count($categoryNames) . " categories and " . count($productData) . " products\n";
    }
}
