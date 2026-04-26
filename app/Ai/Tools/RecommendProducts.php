<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class RecommendProducts extends SparepartsAction
{
    public function description(): Stringable|string
    {
        return 'Get AI-powered product recommendations. Can recommend popular products, products by category, or complementary products based on a given product.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->userId) {
            return 'User ID not set.';
        }

        $strategy = $request->input('strategy', 'popular');
        $categoryId = $request->input('category_id');
        $productId = $request->input('product_id');
        $limit = $request->input('limit', 5);

        $query = Product::query()->with('category')->where('stock_quantity', '>', 0);

        switch ($strategy) {
            case 'popular':
                $query->orderByRaw('RAND()');
                break;

            case 'category':
                if ($categoryId) {
                    $query->where('category_id', $categoryId);
                }
                $query->orderByRaw('RAND()');
                break;

            case 'complementary':
                if (!$productId) {
                    return json_encode(['error' => 'product_id is required for complementary recommendations']);
                }
                $product = Product::find($productId);
                if (!$product) {
                    return json_encode(['error' => 'Product not found']);
                }
                $query->where('category_id', $product->category_id)
                      ->where('id', '!=', $productId);
                break;

            case 'low_stock':
                $query->whereColumn('stock_quantity', '<', 'min_stock_level');
                break;

            default:
                $query->orderByRaw('RAND()');
        }

        $products = $query->limit($limit)->get();

        return json_encode([
            'strategy' => $strategy,
            'count' => $products->count(),
            'products' => $products,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'strategy' => [
                'type' => 'string',
                'enum' => ['popular', 'category', 'complementary', 'low_stock'],
                'description' => 'Recommendation strategy',
            ],
            'category_id' => [
                'type' => 'integer',
                'description' => 'Category ID (for category strategy)',
            ],
            'product_id' => [
                'type' => 'integer',
                'description' => 'Product ID (for complementary strategy)',
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Maximum recommendations to return',
            ],
        ];
    }
}
