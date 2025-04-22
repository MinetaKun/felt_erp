<?php

namespace Database\Seeders;

use App\Models\WoolOrder;
use App\Models\WoolOrderItem;
use App\Models\WoolStock;
use Illuminate\Database\Seeder;

class WoolStockSeeder extends Seeder
{
    public function run()
    {
        // Get all delivered orders
        $deliveredOrders = WoolOrder::where('status', 'delivered')->get();

        foreach ($deliveredOrders as $order) {
            foreach ($order->items as $item) {
                WoolStock::create([
                    'wool_order_item_id' => $item->id,
                    'wool_type' => $item->wool_type,
                    'color' => $item->color,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'received_date' => $order->expected_delivery_date,
                    'notes' => 'Received from order ' . $order->order_number
                ]);
            }
        }

        // If no delivered orders exist, create some sample stock entries
        if (WoolStock::count() === 0) {
            $sampleStock = [
                [
                    'wool_type' => 'Merino Wool',
                    'color' => 'Natural White',
                    'quantity' => 100.00,
                    'unit' => 'kg',
                    'unit_price' => 15.50,
                    'received_date' => now()->subDays(30),
                    'notes' => 'Premium quality merino wool'
                ],
                [
                    'wool_type' => 'Cashmere',
                    'color' => 'Beige',
                    'quantity' => 50.00,
                    'unit' => 'kg',
                    'unit_price' => 25.75,
                    'received_date' => now()->subDays(15),
                    'notes' => 'Fine cashmere wool'
                ],
                [
                    'wool_type' => 'Alpaca',
                    'color' => 'Brown',
                    'quantity' => 75.00,
                    'unit' => 'kg',
                    'unit_price' => 20.00,
                    'received_date' => now()->subDays(7),
                    'notes' => 'Soft alpaca wool'
                ],
                [
                    'wool_type' => 'Merino Wool',
                    'color' => 'Black',
                    'quantity' => 60.00,
                    'unit' => 'kg',
                    'unit_price' => 16.25,
                    'received_date' => now()->subDays(3),
                    'notes' => 'Dyed merino wool'
                ],
                [
                    'wool_type' => 'Cashmere',
                    'color' => 'Gray',
                    'quantity' => 40.00,
                    'unit' => 'kg',
                    'unit_price' => 26.50,
                    'received_date' => now()->subDays(1),
                    'notes' => 'Premium gray cashmere'
                ]
            ];

            foreach ($sampleStock as $stock) {
                WoolStock::create($stock);
            }
        }
    }
}
