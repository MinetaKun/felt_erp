<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['assignments.artisan']);

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by search term
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('client_details', 'like', "%{$search}%");
            });
        }

        // Filter by due date range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->where('due_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->where('due_date', '<=', $request->end_date);
        }

        // Sort orders
        if ($request->has('sort')) {
            $sortParts = explode(':', $request->sort);
            if (count($sortParts) === 2) {
                $column = $sortParts[0];
                $direction = $sortParts[1];
                $query->orderBy($column, $direction);
            }
        } else {
            $query->latest();
        }

        // Paginate results
        $perPage = $request->has('per_page') ? $request->per_page : 15;
        $orders = $query->paginate($perPage);

        return response()->json($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'size' => 'nullable|string|max:50',
            'wool_color' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric',
            'total_quantity' => 'required|integer|min:1',
            'due_date' => 'required|date',
            'wages_per_unit' => 'required|numeric|min:0',
            'client_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'product_photo' => 'nullable|image|max:2048',
        ]);

        // Generate a unique order ID
        $validated['order_id'] = 'ORD-' . strtoupper(Str::random(8));
        $validated['status'] = 'pending';

        // Handle product photo upload
        if ($request->hasFile('product_photo')) {
            $validated['product_photo'] = $request->file('product_photo')->store('orders', 'public');
        }

        DB::beginTransaction();
        try {
            $order = Order::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show($id)
    {
        $order = Order::with(['assignments.artisan'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * Update the specified order.
     */
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'product_name' => 'sometimes|required|string|max:255',
            'size' => 'nullable|string|max:50',
            'wool_color' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric',
            'total_quantity' => 'sometimes|required|integer|min:1',
            'due_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:pending,in_production,approved,dispatched',
            'wages_per_unit' => 'sometimes|required|numeric|min:0',
            'client_details' => 'nullable|string',
            'notes' => 'nullable|string',
            'product_photo' => 'nullable|image|max:2048',
        ]);

        // Handle product photo upload
        if ($request->hasFile('product_photo')) {
            // Delete old photo if exists
            if ($order->product_photo) {
                Storage::disk('public')->delete($order->product_photo);
            }
            $validated['product_photo'] = $request->file('product_photo')->store('orders', 'public');
        }

        DB::beginTransaction();
        try {
            $order->update($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified order.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        // Check if order has assignments
        if ($order->assignments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete order with existing assignments'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Delete product photo if exists
            if ($order->product_photo) {
                Storage::disk('public')->delete($order->product_photo);
            }

            $order->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available artisans for assignment.
     */
    public function getAvailableArtisans(Request $request)
    {
        $departmentId = $request->query('department_id');

        $query = Artisan::query();

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $artisans = $query->with('department')->get();

        return response()->json([
            'success' => true,
            'data' => $artisans
        ]);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,in_production,approved,dispatched',
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $order->fresh()
        ]);
    }
}
