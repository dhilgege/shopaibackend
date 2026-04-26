#!/usr/bin/env php
<?php
/**
 * Simple standalone seeder script
 * Run from: /home/dhilgege/projectt/shopai
 */

// Bootstrap Laravel
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Seeding database...\n";

// Create 5 categories
$categoryNames = [
    'Electronics',
    'Clothing',
    'Food & Beverages',
    'Home & Garden',
    'Automotive Parts',
];

foreach ($categoryNames as $name) {
    \App\Models\Category::factory()->create([
        'name' => $name,
        'description' => "Products related to {$name}",
    ]);
}
echo "✅ Created " . count($categoryNames) . " categories\n";

// Create 20 products
for ($i = 0; $i < 20; $i++) {
    \App\Models\Product::factory()->create();
}
echo "✅ Created 20 products\n";

echo "\n🎉 Database seeded successfully!\n";
echo "Categories: " . \App\Models\Category::count() . "\n";
echo "Products: " . \App\Models\Product::count() . "\n";
