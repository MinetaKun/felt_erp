<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = RawMaterial::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        // Filter by stock status
        if ($request->has('stockStatus')) {
            switch ($request->stockStatus) {
                case 'low':
                    $query->whereRaw('quantity <= min_stock_level');
                    break;
                case 'warning':
                    $query->whereRaw('quantity <= min_stock_level * 1.5')
                        ->whereRaw('quantity > min_stock_level');
                    break;
                case 'good':
                    $query->whereRaw('quantity > min_stock_level * 1.5');
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        $materials = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $materials
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'min_stock_level' => 'required|numeric|min:0',
            'price_per_unit' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $material = RawMaterial::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Raw material created successfully',
                'data' => $material
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create raw material: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create raw material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $material = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:255',
            'color' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|numeric|min:0',
            'unit' => 'sometimes|required|string|max:50',
            'min_stock_level' => 'sometimes|required|numeric|min:0',
            'price_per_unit' => 'sometimes|required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $material->update($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Raw material updated successfully',
                'data' => $material
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update raw material: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update raw material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $material = RawMaterial::findOrFail($id);

        DB::beginTransaction();
        try {
            $material->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Raw material deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete raw material: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete raw material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStock(Request $request, $id)
    {
        $material = RawMaterial::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'operation' => 'required|in:add,subtract'
        ]);

        DB::beginTransaction();
        try {
            if ($validated['operation'] === 'add') {
                $material->quantity += $validated['quantity'];
            } else {
                $material->quantity -= $validated['quantity'];
            }
            $material->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully',
                'data' => $material
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update stock: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getLowStock()
    {
        $lowStock = RawMaterial::whereRaw('quantity <= min_stock_level')->get();

        return response()->json([
            'success' => true,
            'data' => $lowStock
        ]);
    }
}
