<?php

namespace App\Http\Controllers;

use App\Models\FinishedProduct;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinishedProductController extends Controller
{
    public function index(Request $request)
    {
        $query = FinishedProduct::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by size
        if ($request->has('size')) {
            $query->where('size', $request->size);
        }

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'out_of_stock':
                    $query->where('quantity', 0);
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)
                        ->where('quantity', '<=', 10);
                    break;
                case 'in_stock':
                    $query->where('quantity', '>', 10);
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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
            'size' => 'required|string|max:50',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'wage_per_unit' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255'
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
            'size' => 'sometimes|required|string|max:50',
            'quantity' => 'sometimes|required|integer|min:0',
            'price' => 'sometimes|required|numeric|min:0',
            'wage_per_unit' => 'sometimes|required|numeric|min:0',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255'
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
            'quantity' => 'required|integer|min:0',
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
            'status' => 'required|string|in:in_stock,low_stock,out_of_stock'
        ]);

        DB::beginTransaction();
        try {
            $product->update(['status' => $validated['status']]);
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
        $summary = [
            'total_products' => FinishedProduct::count(),
            'total_quantity' => FinishedProduct::sum('quantity'),
            'total_value' => FinishedProduct::sum(DB::raw('quantity * price')),
            'status_breakdown' => [
                'in_stock' => FinishedProduct::where('quantity', '>', 10)->count(),
                'low_stock' => FinishedProduct::where('quantity', '>', 0)
                    ->where('quantity', '<=', 10)
                    ->count(),
                'out_of_stock' => FinishedProduct::where('quantity', 0)->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
