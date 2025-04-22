<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Artisan;
use App\Models\OrderAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderAssignment::with(['order', 'artisan']);

        // Filter by artisan
        if ($request->has('artisan_id') && !empty($request->artisan_id)) {
            $query->where('artisan_id', $request->artisan_id);
        }

        // Filter by order
        if ($request->has('order_id') && !empty($request->order_id)) {
            $query->where('order_id', $request->order_id);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by department
        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->whereHas('artisan', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($q) use ($search) {
                    $q->where('order_id', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('artisan', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Sorting
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

        $assignments = $query->paginate($request->per_page ?? 15);

        return response()->json($assignments);
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'artisan_id' => 'required|exists:artisans,id',
            'assigned_quantity' => 'required|integer|min:1',
            'status' => 'required|in:pending,in_production,completed,approved,dispatched',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $assignment = new OrderAssignment([
                'order_id' => $validated['order_id'],
                'artisan_id' => $validated['artisan_id'],
                'assigned_quantity' => $validated['assigned_quantity'],
                'completed_quantity' => 0,
                'approved_quantity' => 0,
                'rejected_quantity' => 0,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $assignment->save();

            // Update artisan status to active
            $artisan = Artisan::find($validated['artisan_id']);
            if ($artisan && $artisan->status !== 'active') {
                $artisan->status = 'active';
                $artisan->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $assignment,
                'message' => 'Order assigned successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to assign order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markCompleted(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        $validated = $request->validate([
            'completed_quantity' => 'required|integer|min:1|max:' . $assignment->assigned_quantity,
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $assignment->completed_quantity = $validated['completed_quantity'];
            $assignment->notes = $validated['notes'] ?? $assignment->notes;
            $assignment->status = 'completed';
            $assignment->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $assignment,
                'message' => 'Assignment marked as completed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark assignment as completed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark assignment as completed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveOrReject(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'approved_quantity' => 'required_if:action,approve|integer|min:0|max:' . $assignment->completed_quantity,
            'rejected_quantity' => 'required_if:action,reject|integer|min:0|max:' . $assignment->completed_quantity,
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'approve') {
                $assignment->approved_quantity = $validated['approved_quantity'];
                $assignment->rejected_quantity = $assignment->completed_quantity - $validated['approved_quantity'];

                if ($validated['approved_quantity'] > 0) {
                    $assignment->status = 'approved';
                    $assignment->approved_at = now();
                    $assignment->approved_by = Auth::id();
                }

                if ($assignment->rejected_quantity > 0 && isset($validated['rejection_reason'])) {
                    $assignment->rejection_reason = $validated['rejection_reason'];
                }
            } else {
                $assignment->rejected_quantity = $validated['rejected_quantity'];
                $assignment->approved_quantity = $assignment->completed_quantity - $validated['rejected_quantity'];
                $assignment->rejection_reason = $validated['rejection_reason'];

                if ($assignment->approved_quantity > 0) {
                    $assignment->status = 'approved';
                    $assignment->approved_at = now();
                    $assignment->approved_by = Auth::id();
                } else {
                    $assignment->status = 'rejected';
                }
            }

            $assignment->save();

            // Update artisan status
            $artisan = $assignment->artisan;
            if ($artisan) {
                $artisan->updateStatus();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $assignment,
                'message' => 'Assignment ' . ($validated['action'] === 'approve' ? 'approved' : 'rejected')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dispatch(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        // Can only dispatch approved assignments
        if ($assignment->status !== 'approved') {
            return response()->json([
                'success' => false,
                'error' => 'Only approved assignments can be dispatched'
            ], 422);
        }

        $validated = $request->validate([
            'dispatch_date' => 'required|date',
            'dispatch_method' => 'required|string|in:vehicle,runner,courier,pickup',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $assignment->status = 'dispatched';
            $assignment->dispatched_at = now();
            $assignment->dispatched_by = Auth::id();
            $assignment->dispatch_date = $validated['dispatch_date'];
            $assignment->dispatch_method = $validated['dispatch_method'];
            $assignment->dispatch_notes = $validated['notes'] ?? null;
            $assignment->save();

            // Update order status if all assignments are dispatched
            $order = $assignment->order;
            $allDispatched = true;

            foreach ($order->assignments as $orderAssignment) {
                if ($orderAssignment->status !== 'dispatched') {
                    $allDispatched = false;
                    break;
                }
            }

            if ($allDispatched) {
                $order->status = 'dispatched';
                $order->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $assignment,
                'message' => 'Assignment dispatched successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to dispatch assignment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to dispatch assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDispatch(Request $request)
    {
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'required|exists:order_assignments,id',
            'dispatch_date' => 'required|date',
            'dispatch_method' => 'required|string|in:vehicle,runner,courier,pickup',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $dispatchedCount = 0;
            $artisanIds = [];
            $orderIds = [];

            foreach ($validated['assignment_ids'] as $assignmentId) {
                $assignment = OrderAssignment::find($assignmentId);
                if ($assignment && $assignment->status === 'approved') {
                    $assignment->status = 'dispatched';
                    $assignment->dispatched_at = now();
                    $assignment->dispatched_by = Auth::id();
                    $assignment->dispatch_date = $validated['dispatch_date'];
                    $assignment->dispatch_method = $validated['dispatch_method'];
                    $assignment->dispatch_notes = $validated['notes'] ?? null;
                    $assignment->save();
                    $dispatchedCount++;

                    // Collect artisan IDs to update their status later
                    if (!in_array($assignment->artisan_id, $artisanIds)) {
                        $artisanIds[] = $assignment->artisan_id;
                    }

                    // Collect order IDs to check if they should be marked as dispatched
                    if (!in_array($assignment->order_id, $orderIds)) {
                        $orderIds[] = $assignment->order_id;
                    }
                }
            }

            // Update status for all affected artisans
            foreach ($artisanIds as $artisanId) {
                $artisan = Artisan::find($artisanId);
                if ($artisan) {
                    $artisan->updateStatus();
                }
            }

            // Check if any orders should be marked as dispatched
            foreach ($orderIds as $orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $allDispatched = true;

                    foreach ($order->assignments as $assignment) {
                        if ($assignment->status !== 'dispatched') {
                            $allDispatched = false;
                            break;
                        }
                    }

                    if ($allDispatched) {
                        $order->status = 'dispatched';
                        $order->save();
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "$dispatchedCount assignments dispatched successfully",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to bulk dispatch assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to dispatch assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update the assignToOrder method to ensure proper validation of the status field
    public function assignToOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Validate the assignments array with explicit status validation
        $request->validate([
            'assignments' => 'required|array',
            'assignments.*.artisan_id' => 'required|exists:artisans,id',
            'assignments.*.assigned_quantity' => 'required|integer|min:1',
            'assignments.*.status' => 'required|string|in:pending,in_production,completed,approved,dispatched',
            'assignments.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $createdAssignments = [];
            $artisanIds = [];

            foreach ($request->assignments as $assignmentData) {
                $assignment = new OrderAssignment([
                    'order_id' => $id,
                    'artisan_id' => $assignmentData['artisan_id'],
                    'assigned_quantity' => $assignmentData['assigned_quantity'],
                    'completed_quantity' => 0,
                    'approved_quantity' => 0,
                    'rejected_quantity' => 0,
                    'status' => $assignmentData['status'],
                    'notes' => $assignmentData['notes'] ?? null,
                ]);

                $assignment->save();
                $createdAssignments[] = $assignment;

                // Collect artisan IDs to update their status later
                if (!in_array($assignmentData['artisan_id'], $artisanIds)) {
                    $artisanIds[] = $assignmentData['artisan_id'];
                }
            }

            // Update status for all affected artisans
            foreach ($artisanIds as $artisanId) {
                $artisan = Artisan::find($artisanId);
                if ($artisan && $artisan->status !== 'active') {
                    $artisan->status = 'active';
                    $artisan->save();
                }
            }

            // Update order status if needed
            if ($order->status === 'pending') {
                $order->status = 'in_production';
                $order->save();
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $createdAssignments,
                'message' => count($createdAssignments) . ' assignments created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to assign order: ' . $e->getMessage()
            ], 500);
        }
    }
}
