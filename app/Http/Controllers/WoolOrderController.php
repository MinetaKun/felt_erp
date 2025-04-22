<?php

namespace App\Http\Controllers;

use App\Models\WoolOrder;
use App\Models\WoolOrderItem;
use App\Models\WoolStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WoolOrderController extends Controller
{
    public function index()
    {
        $orders = WoolOrder::with(['supplier', 'items'])->get();
        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wool_supplier_id' => 'required|exists:wool_suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.wool_type' => 'required|string|max:255',
            'items.*.color' => 'required|string|max:50',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string|max:20',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.specifications' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate total amount from items
        $totalAmount = 0;
        foreach ($request->items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $order = WoolOrder::create([
            'wool_supplier_id' => $request->wool_supplier_id,
            'order_number' => 'WO-' . Str::random(8),
            'order_date' => $request->order_date,
            'expected_delivery_date' => $request->expected_delivery_date,
            'total_amount' => $totalAmount,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        foreach ($request->items as $item) {
            WoolOrderItem::create([
                'wool_order_id' => $order->id,
                'wool_type' => $item['wool_type'],
                'color' => $item['color'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'specifications' => $item['specifications'] ?? null
            ]);
        }

        return response()->json($order->load('items'), 201);
    }

    public function show($id)
    {
        $order = WoolOrder::with(['supplier', 'items'])->findOrFail($id);
        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $order = WoolOrder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update($request->only(['status', 'notes']));

        // If status is delivered, create stock entries
        if ($request->status === 'delivered') {
            foreach ($order->items as $item) {
                WoolStock::create([
                    'wool_order_item_id' => $item->id,
                    'wool_type' => $item->wool_type,
                    'color' => $item->color,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'received_date' => now()
                ]);
            }
        }

        return response()->json($order->load('items'));
    }

    public function destroy($id)
    {
        $order = WoolOrder::findOrFail($id);
        $order->delete();
        return response()->json(null, 204);
    }
}
