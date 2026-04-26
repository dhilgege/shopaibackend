<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ProductLookup extends SparepartsAction
{
    public function description(): Stringable|string
    {
        return 'Get detailed information about a specific product by ID or SKU. Use this when customers ask for product details.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->userId) {
            return 'User ID not set.';
        }

        $productId = $request->input('product_id');
        $sku = $request->input('sku');

        if (!$productId && !$sku) {
            return json_encode(['error' => 'Either product_id or sku is required']);
        }

        $query = Product::with(['category', 'orderItems']);
        if ($productId) {
            $query->where('id', $productId);
        }
        if ($sku) {
            $query->where('sku', $sku);
        }

        $product = $query->first();

        if (!$product) {
            return json_encode(['error' => 'Product not found']);
        }

        return json_encode([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'sku' => $product->sku,
                'brand' => $product->brand,
                'part_number' => $product->part_number,
                'vehicle_model' => $product->vehicle_model,
                'compatibility' => $product->compatibility,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                ] : null,
                'recent_sales_count' => $product->orderItems()->count(),
            ]
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => [
                'type' => 'integer',
                'description' => 'Product ID',
            ],
            'sku' => [
                'type' => 'string',
                'description' => 'Product SKU',
            ],
        ];
    }
}
