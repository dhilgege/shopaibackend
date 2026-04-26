<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Product;

class RecommendationController extends Controller
{
    public function recommend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $userId = $validated['user_id'];
        $limit = $validated['limit'] ?? 10;

        $recommendedProducts = Product::with('category')
            ->where('stock_quantity', '>', 0)
            ->orderByRaw('RAND()')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $recommendedProducts,
            'message' => 'Product recommendations',
        ]);
    }

    public function complementary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'limit' => 'sometimes|integer|min:1|max:10',
        ]);

        $productId = $validated['product_id'];
        $limit = $validated['limit'] ?? 5;

        $product = Product::findOrFail($productId);

        $complementaryProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $productId)
            ->where('stock_quantity', '>', 0)
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $complementaryProducts,
            'message' => 'Complementary product recommendations',
        ]);
    }
}
