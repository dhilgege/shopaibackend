<?php

namespace App\Ai\Tools;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Illuminate\Support\Facades\DB;

class OrderCreate extends SparepartsAction
{
    public function description(): Stringable|string
    {
        return 'Create a new order with items. Requires user_id and list of products with quantities.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->userId) {
            return 'User ID not set.';
        }

        $userId = $request->input('user_id', $this->userId);
        $items = $request->input('items', []);
        $orderDate = $request->input('order_date', now()->format('Y-m-d H:i:s'));

        if (empty($items)) {
            return json_encode(['error' => 'Order must contain at least one item']);
        }

        foreach ($items as &$item) {
            if (!isset($item['product_id']) || !isset($item['quantity'])) {
                return json_encode(['error' => 'Each item must have product_id and quantity']);
            }
        }

        return DB::transaction(function () use ($userId, $items, $orderDate) {
            $order = Order::create([
                'user_id' => $userId,
                'order_date' => $orderDate,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($items as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $quantity = (int) $itemData['quantity'];

                if ($product->stock_quantity < $quantity) {
                    return json_encode([
                        'error' => "Insufficient stock for product {$product->name}",
                        'available' => $product->stock_quantity,
                        'requested' => $quantity,
                    ]);
                }

                $unitPrice = $product->price;
                $lineTotal = $unitPrice * $quantity;
                $totalAmount += $lineTotal;

                $order->orderItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ]);

                $product->decrement('stock_quantity', $quantity);
            }

            $order->update(['total_amount' => $totalAmount]);

            return json_encode([
                'message' => 'Order created successfully',
                'order' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'order_date' => $order->order_date,
                    'total_amount' => $order->total_amount,
                    'items_count' => $order->orderItems()->count(),
                ]
            ]);
        });
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'user_id' => [
                'type' => 'integer',
                'description' => 'User ID (defaults to current user if not provided)',
            ],
            'order_date' => [
                'type' => 'string',
                'description' => 'Order date (ISO format)',
            ],
            'items' => [
                'type' => 'array',
                'items' => [
                    'type' => 'object',
                    'properties' => [
                        'product_id' => ['type' => 'integer'],
                        'quantity' => ['type' => 'integer'],
                    ],
                ],
                'description' => 'List of products and quantities',
            ],
        ];
    }
}
