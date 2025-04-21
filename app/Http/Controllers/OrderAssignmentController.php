<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class OrderAssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index(Request $request)
    {
        $query = OrderAssignment::with(['order', 'artisan']);

        // Add approved_by and dispatched_by relationships only when needed
        if ($request->has('status') && ($request->status === 'approved' || $request->status === 'dispatched')) {
            $query->with(['approvedByUser', 'dispatchedByUser']);
        }

        // Filter by order
        if ($request->has('order_id') && !empty($request->order_id)) {
            $query->where('order_id', $request->order_id);
        }

        // Filter by artisan
        if ($request->has('artisan_id') && !empty($request->artisan_id)) {
            $query->where('artisan_id', $request->artisan_id);
        }

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Sort assignments
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
        $assignments = $query->paginate($perPage);

        return response()->json($assignments);
    }

    /**
     * Assign orders to artisans.
     */
    public function assign(Request $request)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.order_id' => 'required|exists:orders,id',
            'assignments.*.artisan_id' => 'required|exists:artisans,id',
            'assignments.*.assigned_quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $createdAssignments = [];

            foreach ($validated['assignments'] as $assignment) {
                $order = Order::findOrFail($assignment['order_id']);
                $artisan = Artisan::findOrFail($assignment['artisan_id']);

                // Check if the order has enough remaining quantity
                if ($order->remaining_quantity < $assignment['assigned_quantity']) {
                    throw new \Exception("Order {$order->order_id} does not have enough remaining quantity.");
                }

                // Create the assignment
                $newAssignment = OrderAssignment::create([
                    'order_id' => $assignment['order_id'],
                    'artisan_id' => $assignment['artisan_id'],
                    'assigned_quantity' => $assignment['assigned_quantity'],
                    'completed_quantity' => 0,
                    'approved_quantity' => 0,
                    'rejected_quantity' => 0,
                    'status' => 'pending',
                ]);

                $createdAssignments[] = $newAssignment;

                // Update order status to in_production if it was pending
                if ($order->status === 'pending') {
                    $order->update(['status' => 'in_production']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Orders assigned successfully',
                'data' => $createdAssignments
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order assignment failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update assignment status (mark as completed).
     */
    public function markCompleted(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        $validated = $request->validate([
            'completed_quantity' => 'required|integer|min:1|max:' . $assignment->assigned_quantity,
        ]);

        DB::beginTransaction();
        try {
            $assignment->update([
                'completed_quantity' => $validated['completed_quantity'],
                'status' => 'completed',
            ]);

            // Check if all assignments for this order are completed
            $order = $assignment->order;
            $allCompleted = true;

            foreach ($order->assignments as $orderAssignment) {
                if ($orderAssignment->status !== 'completed' && $orderAssignment->status !== 'approved' && $orderAssignment->status !== 'dispatched') {
                    $allCompleted = false;
                    break;
                }
            }

            // If all assignments are completed, update order status
            if ($allCompleted) {
                $order->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment marked as completed',
                'data' => $assignment->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment completion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark assignment as completed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve or reject completed assignments.
     */
    public function approveOrReject(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        // Ensure the assignment is in completed status
        if ($assignment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Only completed assignments can be approved or rejected'
            ], 422);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'approved_quantity' => 'required_if:action,approve|integer|min:0|max:' . $assignment->completed_quantity,
            'rejected_quantity' => 'required_if:action,reject|integer|min:0|max:' . $assignment->completed_quantity,
            'rejection_reason' => 'required_if:action,reject|nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['action'] === 'approve') {
                $assignment->update([
                    'approved_quantity' => $validated['approved_quantity'],
                    'rejected_quantity' => $assignment->completed_quantity - $validated['approved_quantity'],
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'status' => 'approved',
                ]);
            } else {
                $assignment->update([
                    'rejected_quantity' => $validated['rejected_quantity'],
                    'rejection_reason' => $validated['rejection_reason'],
                    'status' => 'rejected',
                ]);
            }

            // Check if all assignments for this order are approved
            $order = $assignment->order;
            $allApproved = true;

            foreach ($order->assignments as $orderAssignment) {
                if ($orderAssignment->status !== 'approved' && $orderAssignment->status !== 'dispatched') {
                    $allApproved = false;
                    break;
                }
            }

            // If all assignments are approved, update order status
            if ($allApproved) {
                $order->update(['status' => 'approved']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment ' . ($validated['action'] === 'approve' ? 'approved' : 'rejected'),
                'data' => $assignment->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment approval/rejection failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark assignments as dispatched.
     */
    public function markDispatched(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        // Ensure the assignment is in approved status
        if ($assignment->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved assignments can be dispatched'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $assignment->update([
                'dispatched_at' => now(),
                'dispatched_by' => Auth::id(),
                'status' => 'dispatched',
            ]);

            // Check if all assignments for this order are dispatched
            $order = $assignment->order;
            $allDispatched = true;

            foreach ($order->assignments as $orderAssignment) {
                if ($orderAssignment->status !== 'dispatched') {
                    $allDispatched = false;
                    break;
                }
            }

            // If all assignments are dispatched, update order status
            if ($allDispatched) {
                $order->update(['status' => 'dispatched']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment marked as dispatched',
                'data' => $assignment->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment dispatch failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark assignment as dispatched',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve assignments.
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'required|exists:order_assignments,id',
        ]);

        DB::beginTransaction();
        try {
            $approvedAssignments = [];

            foreach ($validated['assignment_ids'] as $assignmentId) {
                $assignment = OrderAssignment::findOrFail($assignmentId);

                // Skip if not in completed status
                if ($assignment->status !== 'completed') {
                    continue;
                }

                $assignment->update([
                    'approved_quantity' => $assignment->completed_quantity,
                    'rejected_quantity' => 0,
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                    'status' => 'approved',
                ]);

                $approvedAssignments[] = $assignment;

                // Check if all assignments for this order are approved
                $order = $assignment->order;
                $allApproved = true;

                foreach ($order->assignments as $orderAssignment) {
                    if ($orderAssignment->status !== 'approved' && $orderAssignment->status !== 'dispatched') {
                        $allApproved = false;
                        break;
                    }
                }

                // If all assignments are approved, update order status
                if ($allApproved) {
                    $order->update(['status' => 'approved']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($approvedAssignments) . ' assignments approved successfully',
                'data' => $approvedAssignments
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk approval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk dispatch assignments.
     */
    public function bulkDispatch(Request $request)
    {
        $validated = $request->validate([
            'assignment_ids' => 'required|array',
            'assignment_ids.*' => 'required|exists:order_assignments,id',
        ]);

        DB::beginTransaction();
        try {
            $dispatchedAssignments = [];

            foreach ($validated['assignment_ids'] as $assignmentId) {
                $assignment = OrderAssignment::findOrFail($assignmentId);

                // Skip if not in approved status
                if ($assignment->status !== 'approved') {
                    continue;
                }

                $assignment->update([
                    'dispatched_at' => now(),
                    'dispatched_by' => Auth::id(),
                    'status' => 'dispatched',
                ]);

                $dispatchedAssignments[] = $assignment;

                // Check if all assignments for this order are dispatched
                $order = $assignment->order;
                $allDispatched = true;

                foreach ($order->assignments as $orderAssignment) {
                    if ($orderAssignment->status !== 'dispatched') {
                        $allDispatched = false;
                        break;
                    }
                }

                // If all assignments are dispatched, update order status
                if ($allDispatched) {
                    $order->update(['status' => 'dispatched']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($dispatchedAssignments) . ' assignments dispatched successfully',
                'data' => $dispatchedAssignments
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk dispatch failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to dispatch assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
