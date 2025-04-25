<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('details', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Filter by size
            if ($request->has('size') && !empty($request->size)) {
                $query->where('size', $request->size);
            }

            // Filter by color
            if ($request->has('color') && !empty($request->color)) {
                $query->where('color', 'like', "%{$request->color}%");
            }

            // Filter by quantity range
            if ($request->has('quantity_range') && !empty($request->quantity_range)) {
                switch ($request->quantity_range) {
                    case 'low':
                        $query->where('quantity', '<', 10);
                        break;
                    case 'medium':
                        $query->whereBetween('quantity', [10, 50]);
                        break;
                    case 'high':
                        $query->where('quantity', '>', 50);
                        break;
                }
            }

            // Get total count before pagination
            $total = $query->count();

            // Paginate results
            $perPage = $request->has('per_page') ? $request->per_page : 15;
            $products = $query->paginate($perPage);

            // Get unique sizes and colors for filters
            $sizes = Product::distinct()->pluck('size')->filter()->values();
            $colors = Product::distinct()->pluck('color')->filter()->values();

            return response()->json([
                'success' => true,
                'data' => $products,
                'sizes' => $sizes,
                'colors' => $colors
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:0',
                'size' => 'required|string|max:50',
                'color' => 'required|string|max:50',
                'details' => 'nullable|string',
                'image_path' => 'nullable|string'
            ]);

            $product = new Product();
            $product->fill($validated);

            // Handle image upload if it's a base64 string
            if (isset($validated['image_path']) && Str::startsWith($validated['image_path'], 'data:image')) {
                $image = $validated['image_path'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace('data:image/jpg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);

                $imageName = 'product_' . time() . '_' . Str::random(10) . '.png';
                Storage::disk('public')->put('products/' . $imageName, base64_decode($image));

                $product->image_path = 'products/' . $imageName;
            }

            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'quantity' => 'sometimes|required|integer|min:0',
                'size' => 'sometimes|required|string|max:50',
                'color' => 'sometimes|required|string|max:50',
                'details' => 'nullable|string',
                'image_path' => 'nullable|string'
            ]);

            // Handle image upload if it's a base64 string
            if (isset($validated['image_path']) && Str::startsWith($validated['image_path'], 'data:image')) {
                // Delete old image if exists
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }

                $image = $validated['image_path'];
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace('data:image/jpg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);

                $imageName = 'product_' . time() . '_' . Str::random(10) . '.png';
                Storage::disk('public')->put('products/' . $imageName, base64_decode($image));

                $validated['image_path'] = 'products/' . $imageName;
            }

            $product->fill($validated);
            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            // Delete image if exists
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
