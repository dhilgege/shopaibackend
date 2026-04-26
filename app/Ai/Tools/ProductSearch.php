<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ProductSearch extends SparepartsAction
{
    public function description(): Stringable|string
    {
        return 'Search for products by name, brand, part number, or vehicle model. Use this when customers ask about available parts.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->userId) {
            return 'User ID not set.';
        }

        $query = $request->input('query', '');
        $filters = $request->input('filters', []);

        $products = Product::query()->with('category');

        if ($query) {
            $products->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('part_number', 'like', "%{$query}%")
                  ->orWhere('brand', 'like', "%{$query}%")
                  ->orWhere('vehicle_model', 'like', "%{$query}%");
            });
        }

        if (isset($filters['category_id'])) {
            $products->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand'])) {
            $products->where('brand', $filters['brand']);
        }

        if (isset($filters['vehicle_model'])) {
            $products->where('vehicle_model', 'like', "%{$filters['vehicle_model']}%");
        }

        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $products->where('stock_quantity', '>', 0);
        }

        $limit = $request->input('limit', 10);
        $results = $products->limit($limit)->get();

        return json_encode([
            'count' => $results->count(),
            'products' => $results,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => [
                'type' => 'string',
                'description' => 'Search query (name, part number, brand, vehicle model)',
            ],
            'filters' => [
                'type' => 'object',
                'properties' => [
                    'category_id' => ['type' => 'integer'],
                    'brand' => ['type' => 'string'],
                    'vehicle_model' => ['type' => 'string'],
                    'in_stock' => ['type' => 'boolean'],
                ],
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Maximum number of results (default: 10)',
            ],
        ];
    }
}
