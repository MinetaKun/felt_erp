<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use App\Models\Order;
use App\Models\OrderAssignment;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ArtisanProductivityController extends Controller
{
    public function index(Request $request)
    {
        // Start with a base query
        $query = Artisan::with(['department', 'orderAssignments', 'attendances']);

        // Apply department filter if specified
        if ($request->has('department') && !empty($request->department)) {
            $query->where('department_id', $request->department);
        }

        // Get paginated results
        $perPage = $request->input('per_page', 10);
        $artisans = $query->paginate($perPage);

        // Transform the data
        $transformedData = collect($artisans->items())->map(function ($artisan) {
            // Calculate attendance metrics
            $totalDays = $artisan->attendances->count();
            $presentDays = $artisan->attendances->where('status', 'present')->count();
            $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

            // Calculate order metrics
            $assignments = $artisan->orderAssignments;
            $totalAssignments = $assignments->count();
            $completedAssignments = $assignments->whereIn('status', ['completed', 'dispatched'])->count();
            $approvedAssignments = $assignments->whereIn('status', ['approved', 'dispatched'])->count();

            // Calculate quantities
            $totalAssignedQuantity = $assignments->sum('assigned_quantity');
            $totalCompletedQuantity = $assignments->whereIn('status', ['completed', 'dispatched'])->sum('completed_quantity');
            $totalApprovedQuantity = $assignments->whereIn('status', ['approved', 'dispatched'])->sum('approved_quantity');

            return [
                'id' => $artisan->id,
                'name' => $artisan->name,
                'profile_photo' => $artisan->profile_photo,
                'department' => $artisan->department,
                'attendance' => [
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                    'attendance_rate' => round($attendanceRate, 2)
                ],
                'orders' => [
                    'total_assignments' => $totalAssignments,
                    'completed_assignments' => $completedAssignments,
                    'approved_assignments' => $approvedAssignments,
                    'completion_rate' => $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0,
                    'approval_rate' => $completedAssignments > 0 ? round(($approvedAssignments / $completedAssignments) * 100, 2) : 0
                ],
                'products' => [
                    'total_assigned' => $totalAssignedQuantity,
                    'total_completed' => $totalCompletedQuantity,
                    'total_approved' => $totalApprovedQuantity,
                    'average_per_day' => $totalDays > 0 ? round($totalApprovedQuantity / $totalDays, 2) : 0
                ]
            ];
        });

        // Calculate overall metrics
        $totalArtisans = $artisans->total();
        $totalProducts = $transformedData->sum('products.total_approved');
        $averageAttendanceRate = $transformedData->avg('attendance.attendance_rate');
        $averageCompletionRate = $transformedData->avg('orders.completion_rate');

        return response()->json([
            'data' => $transformedData->values()->all(),
            'meta' => [
                'total_artisans' => $totalArtisans,
                'total_products' => $totalProducts,
                'average_attendance_rate' => round($averageAttendanceRate, 2),
                'average_completion_rate' => round($averageCompletionRate, 2),
                'current_page' => $artisans->currentPage(),
                'last_page' => $artisans->lastPage(),
                'per_page' => $artisans->perPage(),
                'total' => $artisans->total()
            ]
        ]);
    }
}
