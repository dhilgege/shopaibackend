<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class InventoryCheck extends SparepartsAction
{
    public function description(): Stringable|string
    {
        return 'Check inventory levels for products. Can check specific product by ID/SKU or list all low stock items.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->userId) {
            return 'User ID not set.';
        }

        $productId = $request->input('product_id');
        $sku = $request->input('sku');
        $checkLowStock = $request->input('check_low_stock', false);

        if ($productId || $sku) {
            $query = Product::query();
            if ($productId) {
                $query->where('id', $productId);
            }
            if ($sku) {
                $query->where('sku', $sku);
            }
            $product = $query->with('category')->first();

            if (!$product) {
                return json_encode(['error' => 'Product not found']);
            }

            return json_encode([
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock_quantity' => $product->stock_quantity,
                    'min_stock_level' => $product->min_stock_level,
                    'is_low_stock' => $product->stock_quantity < $product->min_stock_level,
                    'is_out_of_stock' => $product->stock_quantity == 0,
                ]
            ]);
        }

        if ($checkLowStock) {
            $lowStock = Product::whereColumn('stock_quantity', '<', 'min_stock_level')
                ->with('category')
                ->get();
            return json_encode([
                'low_stock_count' => $lowStock->count(),
                'products' => $lowStock,
            ]);
        }

        $allProducts = Product::select('id', 'name', 'sku', 'stock_quantity', 'min_stock_level')
            ->get();
        return json_encode(['products' => $allProducts]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => [
                'type' => 'integer',
                'description' => 'Check specific product by ID',
            ],
            'sku' => [
                'type' => 'string',
                'description' => 'Check specific product by SKU',
            ],
            'check_low_stock' => [
                'type' => 'boolean',
                'description' => 'Return all low stock products',
            ],
        ];
    }
}
