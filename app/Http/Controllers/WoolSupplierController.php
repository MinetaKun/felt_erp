<?php

namespace App\Http\Controllers;

use App\Models\WoolSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WoolSupplierController extends Controller
{
    public function index()
    {
        $suppliers = WoolSupplier::withTrashed()->get();
        return response()->json($suppliers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'tax_id' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier = WoolSupplier::create($request->all());
        return response()->json($supplier, 201);
    }

    public function show($id)
    {
        $supplier = WoolSupplier::withTrashed()->with('orders')->findOrFail($id);
        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = WoolSupplier::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'tax_id' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $supplier->update($request->all());
        return response()->json($supplier);
    }

    public function destroy($id)
    {
        $supplier = WoolSupplier::withTrashed()->findOrFail($id);

        if ($supplier->trashed()) {
            return response()->json([
                'message' => 'This supplier has already been deleted.',
                'deleted_at' => $supplier->deleted_at
            ], 410);
        }

        $supplier->delete();
        return response()->json(null, 204);
    }

    public function restore($id)
    {
        $supplier = WoolSupplier::withTrashed()->findOrFail($id);
        $supplier->restore();
        return response()->json($supplier);
    }
}
