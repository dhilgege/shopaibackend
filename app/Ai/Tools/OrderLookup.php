<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class OrderLookup extends SparepartsAction
{
    public function description(): Stringable|string
    {
        return 'Look up orders by ID, user ID, or date range. Shows order details including items and totals.';
    }

    public function handle(Request $request): Stringable|string
    {
        if (! $this->userId) {
            return 'User ID not set.';
        }

        $orderId = $request->input('order_id');
        $userId = $request->input('user_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $limit = $request->input('limit', 10);

        $query = Order::query()->with(['user', 'orderItems.product']);

        if ($orderId) {
            $query->where('id', $orderId);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('user_id', $this->userId);
        }

        if ($startDate) {
            $query->where('order_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('order_date', '<=', $endDate);
        }

        $orders = $query->orderBy('order_date', 'desc')->limit($limit)->get();

        $result = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'user_id' => $order->user_id,
                'order_date' => $order->order_date,
                'total_amount' => $order->total_amount,
                'items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->quantity * $item->unit_price,
                    ];
                }),
            ];
        });

        return json_encode([
            'count' => $result->count(),
            'orders' => $result,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => [
                'type' => 'integer',
                'description' => 'Specific order ID to look up',
            ],
            'user_id' => [
                'type' => 'integer',
                'description' => 'User ID (defaults to current user)',
            ],
            'start_date' => [
                'type' => 'string',
                'description' => 'Start date (YYYY-MM-DD)',
            ],
            'end_date' => [
                'type' => 'string',
                'description' => 'End date (YYYY-MM-DD)',
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Maximum orders to return',
            ],
        ];
    }
}
