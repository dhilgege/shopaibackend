<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function insights(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'days_ahead' => 'sometimes|integer|min:1|max:365',
        ]);

        $daysAhead = $validated['days_ahead'] ?? 30;

        $lowStockProducts = Product::whereColumn('stock_quantity', '<', 'min_stock_level')
            ->with('category')
            ->get();

        $outOfStockProducts = Product::where('stock_quantity', 0)
            ->with('category')
            ->get();

        $overstockedProducts = Product::whereColumn('stock_quantity', '>', DB::raw('min_stock_level * 3'))
            ->with('category')
            ->get();

        $totalInventoryValue = Product::sum(DB::raw('stock_quantity * price'));

        $recentSales = OrderItem::select(
            DB::raw('SUM(quantity) as total_sold'),
            DB::raw('DATE(created_at) as sale_date')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('sale_date')
        ->orderBy('sale_date')
        ->get();

        return response()->json([
            'data' => [
                'low_stock_products' => $lowStockProducts,
                'out_of_stock_products' => $outOfStockProducts,
                'overstocked_products' => $overstockedProducts,
                'total_inventory_value' => round($totalInventoryValue, 2),
                'recent_sales_trend' => $recentSales,
                'days_ahead_forecast' => $daysAhead,
            ],
            'message' => 'Inventory insights',
        ]);
    }

    public function reorderRecommendations(Request $request): JsonResponse
    {
        $productsNeedingReorder = Product::whereColumn('stock_quantity', '<', 'min_stock_level')
            ->with('category')
            ->get();

        $recommendations = $productsNeedingReorder->map(function ($product) {
            $suggestedQuantity = max(0, ($product->min_stock_level * 2) - $product->stock_quantity);

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'current_stock' => $product->stock_quantity,
                'min_stock_level' => $product->min_stock_level,
                'suggested_reorder_quantity' => $suggestedQuantity,
                'category' => $product->category->name ?? null,
            ];
        });

        return response()->json([
            'data' => $recommendations,
            'message' => 'Reorder recommendations',
        ]);
    }
}
