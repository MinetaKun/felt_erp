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
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $assignment = new OrderAssignment([
                'order_id' => $validated['order_id'],
                'artisan_id' => $validated['artisan_id'],
                'assigned_quantity' => $validated['assigned_quantity'],
                'approved_quantity' => 0,
                'rejected_quantity' => 0,
                'status' => 'in_production', // Always set to in_production when assigned
                'notes' => $validated['notes'] ?? null,
            ]);

            $assignment->save();

            // Update order status to in_production
            $order = Order::find($validated['order_id']);
            if ($order) {
                $order->status = 'in_production';
                $order->save();
            }

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
    public function markDispatched(Request $request, $id)
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

    /**
     * Approve or reject an assignment.
     */
    public function approveOrReject(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'approved_quantity' => 'required_if:action,approve|integer|min:0|max:' . $assignment->assigned_quantity,
            'rejected_quantity' => 'required_if:action,reject|integer|min:0|max:' . $assignment->assigned_quantity,

        ]);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'approve') {
                $assignment->approved_quantity = $validated['approved_quantity'];
                $assignment->rejected_quantity = $assignment->assigned_quantity - $validated['approved_quantity'];

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
                $assignment->approved_quantity = $assignment->assigned_quantity - $validated['rejected_quantity'];
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

            // Check if all assignments for this order are processed
            $this->updateOrderStatus($assignment->order);

            DB::commit();
            return response()->json([
                'success' => true,
                'data' => $assignment,
                'message' => 'Assignment ' . ($validated['action'] === 'approve' ? 'approved' : 'rejected') . ' successfully'
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

    /**
     * Bulk approve or reject assignments.
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'required|exists:order_assignments,id',
            'approvals' => 'required|array',
            'approvals.*.id' => 'required|exists:order_assignments,id',
            'approvals.*.approved_quantity' => 'required|integer|min:0',
            'approvals.*.rejection_reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $processedOrders = [];

            foreach ($validated['approvals'] as $approval) {
                $assignment = OrderAssignment::findOrFail($approval['id']);

                // Ensure approved quantity doesn't exceed assigned quantity
                $approvedQuantity = min($approval['approved_quantity'], $assignment->assigned_quantity);
                $rejectedQuantity = $assignment->assigned_quantity - $approvedQuantity;

                $assignment->approved_quantity = $approvedQuantity;
                $assignment->rejected_quantity = $rejectedQuantity;

                if ($approvedQuantity > 0) {
                    $assignment->status = 'approved';
                    $assignment->approved_at = now();
                    $assignment->approved_by = Auth::id();
                }

                if ($rejectedQuantity > 0 && !empty($approval['rejection_reason'])) {
                    $assignment->rejection_reason = $approval['rejection_reason'];
                }

                $assignment->save();

                // Track processed orders for status update
                if (!in_array($assignment->order_id, $processedOrders)) {
                    $processedOrders[] = $assignment->order_id;
                }

                // Update artisan status
                $artisan = $assignment->artisan;
                if ($artisan) {
                    $artisan->updateStatus();
                }
            }

            // Update status for all affected orders
            foreach ($processedOrders as $orderId) {
                $order = Order::find($orderId);
                if ($order) {
                    $this->updateOrderStatus($order);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Assignments processed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process bulk assignments: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to process bulk assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status based on assignments.
     */
    private function updateOrderStatus($order)
    {
        // Reload order with fresh assignments
        $order = Order::with('assignments')->find($order->id);

        if ($order->assignments->isEmpty()) {
            $order->status = 'pending';
            $order->save();
            return;
        }

        // Count assignments by status
        $totalAssignments = $order->assignments->count();
        $approvedAssignments = $order->assignments->where('status', 'approved')->count();
        $dispatchedAssignments = $order->assignments->where('status', 'dispatched')->count();
        $rejectedAssignments = $order->assignments->where('status', 'rejected')->count();

        // Calculate total quantities
        $totalAssignedQuantity = $order->assignments->sum('assigned_quantity');
        $totalApprovedQuantity = $order->assignments->sum('approved_quantity');
        $totalRejectedQuantity = $order->assignments->sum('rejected_quantity');

        // Determine order status based on assignments
        if ($dispatchedAssignments === $totalAssignments) {
            // All assignments are dispatched
            $order->status = 'dispatched';
        } else if ($approvedAssignments + $rejectedAssignments === $totalAssignments) {
            // All assignments are either approved or rejected
            if ($totalApprovedQuantity >= $order->total_quantity) {
                // All required quantity is approved
                $order->status = 'approved';
            } else if ($totalApprovedQuantity + $totalRejectedQuantity >= $totalAssignedQuantity) {
                // All assigned quantity is accounted for (approved + rejected)
                // But not all required quantity is approved, so keep in production
                $order->status = 'in_production';
            } else {
                // Some assignments are still pending
                $order->status = 'in_production';
            }
        } else {
            // Some assignments are still in production
            $order->status = 'in_production';
        }

        $order->save();
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
