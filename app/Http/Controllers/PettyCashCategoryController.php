<?php

namespace App\Http\Controllers;

use App\Models\PettyCashCategory;
use Illuminate\Http\Request;

class PettyCashCategoryController extends Controller
{
    public function index()
    {
        return response()->json(PettyCashCategory::all());
    }

    public function show(PettyCashCategory $category)
    {
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:petty_cash_categories,name',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $category = PettyCashCategory::create($validated);

        return response()->json($category, 201);
    }

    public function update(Request $request, PettyCashCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:petty_cash_categories,name,' . $category->id,
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(PettyCashCategory $category)
    {
        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}
