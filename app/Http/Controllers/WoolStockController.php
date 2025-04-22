<?php

namespace App\Http\Controllers;

use App\Models\WoolStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Support\Facades\Log;

class WoolStockController extends Controller
{
    public function index()
    {
        $stock = WoolStock::with('orderItem.order')->get();
        return response()->json($stock);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'wool_type' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|max:20',
            'unit_price' => 'required|numeric|min:0',
            'received_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $stock = WoolStock::findOrFail($id);
        $stock->update($request->all());

        return response()->json($stock);
    }

    public function destroy($id)
    {
        $stock = WoolStock::findOrFail($id);
        $stock->delete();
        return response()->json(null, 204);
    }

    public function export()
    {
        try {
            $stock = WoolStock::with('orderItem.order')->get();

            if ($stock->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No stock found to export'
                ], 404);
            }

            $csv = Writer::createFromFileObject(new SplTempFileObject());

            $csv->insertOne([
                'Wool Type',
                'Color',
                'Quantity',
                'Unit',
                'Unit Price',
                'Received Date',
                'Order Reference',
                'Notes'
            ]);

            foreach ($stock as $item) {
                $csv->insertOne([
                    $item->wool_type,
                    $item->color,
                    $item->quantity,
                    $item->unit,
                    $item->unit_price,
                    $item->received_date,
                    $item->orderItem?->order?->order_number ?? 'N/A',
                    $item->notes
                ]);
            }

            $filename = 'wool_stock_export_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = (string) $csv;

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));
        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV: ' . $e->getMessage()
            ], 500);
        }
    }
}
