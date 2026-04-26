<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    public function index(string $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $items = $order->orderItems()->with('product')->get();

        return response()->json(['data' => $items]);
    }

    public function store(Request $request, string $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $unitPrice = $product->price;
        $quantity = $validated['quantity'];

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
        ]);

        $product->decrement('stock_quantity', $quantity);

        $order->updateTotalAmount();

        return response()->json(['data' => $item->load('product')], 201);
    }

    public function show(string $orderId, string $itemId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $item = $order->orderItems()->with('product')->findOrFail($itemId);

        return response()->json(['data' => $item]);
    }

    public function update(Request $request, string $orderId, string $itemId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $item = $order->orderItems()->findOrFail($itemId);

        $validated = $request->validate([
            'quantity' => 'sometimes|required|integer|min:1',
        ]);

        if (isset($validated['quantity'])) {
            $oldQuantity = $item->quantity;
            $newQuantity = $validated['quantity'];
            $quantityDiff = $newQuantity - $oldQuantity;

            $item->update(['quantity' => $newQuantity]);

            if ($quantityDiff !== 0) {
                $item->product->decrement('stock_quantity', $quantityDiff);
            }

            $order->updateTotalAmount();
        }

        return response()->json(['data' => $item->load('product')]);
    }

    public function destroy(string $orderId, string $itemId): JsonResponse
    {
        $order = Order::findOrFail($orderId);
        $item = $order->orderItems()->findOrFail($itemId);

        $item->product->increment('stock_quantity', $item->quantity);
        $item->delete();

        $order->updateTotalAmount();

        return response()->json(null, 204);
    }
}
