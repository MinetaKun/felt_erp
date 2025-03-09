<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Employee;
use App\Models\OrderArtisanAssignment;

class OrderController extends Controller
{

    // Show list of orders with assignments
    public function index()
    {
        // Fetch orders without the related assignments and artisans for now
        return response()->json(Order::all());
    }


    // Store a new order
    public function store(Request $request)
    {
        $order = Order::create($request->all());
        return response()->json($order);
    }

    // Assign artisan(s) to an order
    public function assignArtisan(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Get the artisan (employee) by employee_id
        $artisan = Employee::findOrFail($request->employee_id);

        // Check if the employee is an artisan
        if ($artisan->employee_type !== 'artisan') {
            return response()->json(['error' => 'Selected employee is not an artisan'], 400);
        }

        // Create the order-artisan assignment
        $assignment = OrderArtisanAssignment::create([
            'order_id' => $order->id,
            'employee_id' => $artisan->id,
            'assigned_quantity' => $request->assigned_quantity
        ]);

        return response()->json($assignment);
    }
}
