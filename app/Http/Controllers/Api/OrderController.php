<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'orderItems.product']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $orders = $query->orderBy('order_date', 'desc')->paginate(15);

        return response()->json(['data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $orderDate = $validated['order_date'] ?? now();

        return DB::transaction(function () use ($validated, $orderDate) {
            $totalAmount = 0;
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'order_date' => $orderDate,
                'total_amount' => 0,
            ]);

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);

                $unitPrice = $product->price;
                $quantity = $itemData['quantity'];
                $lineTotal = $unitPrice * $quantity;
                $totalAmount += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $product->decrement('stock_quantity', $quantity);
            }

            $order->update(['total_amount' => $totalAmount]);

            return response()->json(['data' => $order->load(['orderItems.product'])], 201);
        });
    }

    public function show(string $id): JsonResponse
    {
        $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);
        return response()->json(['data' => $order]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'order_date' => 'sometimes|required|date',
        ]);

        $order->update($validated);

        return response()->json(['data' => $order]);
    }

    public function destroy(string $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        return DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
            $order->orderItems()->delete();
            $order->delete();

            return response()->json(null, 204);
        });
    }
}
