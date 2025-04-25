<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Artisan;
use App\Models\OrderAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\OrderNotification;
use App\Notifications\OrderStatusChanged;

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

    public function assign(Request $request, Order $order)
    {
        $request->validate([
            'artisan_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'due_date' => 'required|date|after:today',
        ]);

        // Check if artisan is already assigned to this order
        $existingAssignment = OrderAssignment::where('order_id', $order->id)
            ->where('artisan_id', $request->artisan_id)
            ->first();

        if ($existingAssignment) {
            return response()->json([
                'message' => 'This artisan is already assigned to this order'
            ], 422);
        }

        // Create new assignment
        $assignment = OrderAssignment::create([
            'order_id' => $order->id,
            'artisan_id' => $request->artisan_id,
            'quantity' => $request->quantity,
            'due_date' => $request->due_date,
            'status' => 'pending'
        ]);

        // Update order status
        $order->status = 'in_progress';
        $order->save();

        // Send notification to admin users
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super-admin', 'admin']);
        })->get();

        foreach ($adminUsers as $admin) {
            $admin->notify(new OrderStatusChanged(
                $order,
                'pending',
                'in_progress',
                "Order #{$order->order_number} has been assigned to an artisan"
            ));
        }

        return response()->json([
            'message' => 'Order assigned successfully',
            'assignment' => $assignment
        ]);
    }

    public function markDispatched(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);
        $order = $assignment->order;

        $validated = $request->validate([
            'dispatch_date' => 'required|date',
            'dispatch_method' => 'required|string',
            'dispatch_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $assignment->update([
                'dispatch_date' => $validated['dispatch_date'],
                'dispatch_method' => $validated['dispatch_method'],
                'dispatch_notes' => $validated['dispatch_notes'],
                'status' => 'dispatched',
                'dispatched_at' => now(),
                'dispatched_by' => auth()->id(),
            ]);

            // Update order status and send notification
            $oldStatus = $order->status;
            $newStatus = $this->updateOrderStatus($order);

            if ($oldStatus !== $newStatus) {
                // Send notification to all users who should be notified
                $users = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['super-admin', 'admin']);
                })->get();

                foreach ($users as $user) {
                    $user->notify(new OrderStatusChanged(
                        $order,
                        $oldStatus,
                        $newStatus,
                        "Order #{$order->order_id} status changed from {$oldStatus} to {$newStatus}"
                    ));
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment marked as dispatched successfully',
                'data' => $assignment->fresh(['order', 'artisan'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark assignment as dispatched: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark assignment as dispatched',
                'error' => $e->getMessage()
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
        $oldStatus = $order->status;
        $newStatus = $order->status;

        // Check if all assignments are dispatched
        if ($order->assignments->every(function ($assignment) {
            return $assignment->status === 'dispatched';
        })) {
            $newStatus = 'dispatched';
        }
        // Check if any assignments are approved
        elseif ($order->assignments->contains('status', 'approved')) {
            $newStatus = 'approved';
        }
        // Check if any assignments are completed
        elseif ($order->assignments->contains('status', 'completed')) {
            $newStatus = 'in_production';
        }
        // Check if any assignments are in production
        elseif ($order->assignments->contains('status', 'in_production')) {
            $newStatus = 'in_production';
        }

        if ($oldStatus !== $newStatus) {
            $order->update(['status' => $newStatus]);

            // Send notification to all users who should be notified
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['super-admin', 'admin']);
            })->get();

            foreach ($users as $user) {
                $user->notify(new OrderStatusChanged($order, $oldStatus, $newStatus));
            }
        }

        return $newStatus;
    }

    // Update the assignToOrder method to ensure proper validation of the status field
    public function assignToOrder(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.artisan_id' => 'required|exists:artisans,id',
            'assignments.*.assigned_quantity' => 'required|integer|min:1',
            'assignments.*.status' => 'required|in:pending,in_production,completed,approved,dispatched',
            'assignments.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['assignments'] as $assignmentData) {
                $assignment = new OrderAssignment($assignmentData);
                $assignment->order_id = $order->id;
                $assignment->save();
            }

            // Update order status and send notification
            $oldStatus = $order->status;
            $newStatus = $this->updateOrderStatus($order);

            if ($oldStatus !== $newStatus) {
                // Send notification to all users who should be notified
                $users = User::whereHas('roles', function ($query) {
                    $query->whereIn('name', ['super-admin', 'admin']);
                })->get();

                foreach ($users as $user) {
                    $user->notify(new OrderStatusChanged($order, $oldStatus, $newStatus));
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignments created successfully',
                'data' => $order->fresh(['assignments.artisan'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate and download a dispatch challan for a specific assignment.
     */
    public function downloadDispatchChallan($id)
    {
        try {
            // Find the assignment with related data
            $assignment = OrderAssignment::with(['order', 'artisan'])->findOrFail($id);

            // Check if the assignment is dispatched
            if ($assignment->status !== 'dispatched') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot generate challan for non-dispatched assignment'
                ], 422);
            }

            // Get the users who approved and dispatched
            $approvedBy = null;
            $dispatchedBy = null;

            if ($assignment->approved_by) {
                $approvedBy = User::find($assignment->approved_by);
            }

            if ($assignment->dispatched_by) {
                $dispatchedBy = User::find($assignment->dispatched_by);
            }

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.dispatch-challan', [
                'assignment' => $assignment,
                'approvedBy' => $approvedBy,
                'dispatchedBy' => $dispatchedBy
            ]);

            // Set filename
            $filename = 'dispatch_challan_' . $assignment->id . '_' . date('Ymd') . '.pdf';

            // Return the PDF for download
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Failed to generate dispatch challan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate dispatch challan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified order assignment.
     */
    public function update(Request $request, $id)
    {
        $assignment = OrderAssignment::findOrFail($id);

        $validated = $request->validate([
            'assigned_quantity' => 'sometimes|required|integer|min:1',
            'completed_quantity' => 'sometimes|required|integer|min:0',
            'approved_quantity' => 'sometimes|required|integer|min:0',
            'rejected_quantity' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|required|in:pending,in_production,completed,approved,dispatched',
            'notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $assignment->update($validated);

            // Update order status if needed
            $this->updateOrderStatus($assignment->order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'data' => $assignment->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified order assignment.
     */
    public function destroy($id)
    {
        $assignment = OrderAssignment::findOrFail($id);
        $order = $assignment->order;

        DB::beginTransaction();
        try {
            $assignment->delete();

            // Update order status after deletion
            $this->updateOrderStatus($order);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Assignment deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
