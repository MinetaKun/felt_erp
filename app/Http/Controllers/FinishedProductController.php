<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinishedProductController extends Controller
{
    public function index(Request $request)
    {
        $query = FinishedProduct::with('order');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        // Filter by size
        if ($request->has('size')) {
            $query->where('size', $request->size);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_id', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'size' => 'required|string|max:50',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:available,reserved,sold'
        ]);

        DB::beginTransaction();
        try {
            $product = FinishedProduct::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Finished product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create finished product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create finished product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = FinishedProduct::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:255',
            'size' => 'sometimes|required|string|max:50',
            'quantity' => 'sometimes|required|integer|min:1',
            'price' => 'sometimes|required|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:available,reserved,sold'
        ]);

        DB::beginTransaction();
        try {
            $product->update($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Finished product updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update finished product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update finished product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $product = FinishedProduct::findOrFail($id);

        DB::beginTransaction();
        try {
            $product->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Finished product deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete finished product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete finished product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateQuantity(Request $request, $id)
    {
        $product = FinishedProduct::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'operation' => 'required|in:add,subtract'
        ]);

        DB::beginTransaction();
        try {
            $product->updateQuantity($validated['quantity'], $validated['operation']);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update quantity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $product = FinishedProduct::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:available,reserved,sold'
        ]);

        DB::beginTransaction();
        try {
            $product->updateStatus($validated['status']);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getInventorySummary()
    {
        $summary = FinishedProduct::selectRaw('
            type,
            color,
            size,
            SUM(quantity) as total_quantity,
            SUM(quantity * price) as total_value
        ')
            ->groupBy('type', 'color', 'size')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
