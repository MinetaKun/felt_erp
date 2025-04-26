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
        $transformedData = collect($artisans->items())->map(function ($artisan) use ($request) {
            // Filter attendance based on date range if specified
            $attendances = $artisan->attendances;
            if ($request->has('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $attendances = $attendances->filter(function ($attendance) use ($now) {
                            return Carbon::parse($attendance->date)->format('Y-m-d') === $now->format('Y-m-d');
                        });
                        break;
                    case 'week':
                        $startOfWeek = $now->copy()->startOfWeek();
                        $endOfWeek = $now->copy()->endOfWeek();
                        $attendances = $attendances->filter(function ($attendance) use ($startOfWeek, $endOfWeek) {
                            $date = Carbon::parse($attendance->date);
                            return $date->between($startOfWeek, $endOfWeek);
                        });
                        break;
                    case 'month':
                        $startOfMonth = $now->copy()->startOfMonth();
                        $endOfMonth = $now->copy()->endOfMonth();
                        $attendances = $attendances->filter(function ($attendance) use ($startOfMonth, $endOfMonth) {
                            $date = Carbon::parse($attendance->date);
                            return $date->between($startOfMonth, $endOfMonth);
                        });
                        break;
                    case 'year':
                        $startOfYear = $now->copy()->startOfYear();
                        $endOfYear = $now->copy()->endOfYear();
                        $attendances = $attendances->filter(function ($attendance) use ($startOfYear, $endOfYear) {
                            $date = Carbon::parse($attendance->date);
                            return $date->between($startOfYear, $endOfYear);
                        });
                        break;
                }
            }

            // Calculate attendance metrics
            $totalDays = $attendances->count();
            $presentDays = $attendances->where('status', 'present')->count();
            $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

            // Filter assignments based on date range if specified
            $assignments = $artisan->orderAssignments;
            if ($request->has('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $assignments = $assignments->filter(function ($assignment) use ($now) {
                            return Carbon::parse($assignment->created_at)->format('Y-m-d') === $now->format('Y-m-d');
                        });
                        break;
                    case 'week':
                        $startOfWeek = $now->copy()->startOfWeek();
                        $endOfWeek = $now->copy()->endOfWeek();
                        $assignments = $assignments->filter(function ($assignment) use ($startOfWeek, $endOfWeek) {
                            $date = Carbon::parse($assignment->created_at);
                            return $date->between($startOfWeek, $endOfWeek);
                        });
                        break;
                    case 'month':
                        $startOfMonth = $now->copy()->startOfMonth();
                        $endOfMonth = $now->copy()->endOfMonth();
                        $assignments = $assignments->filter(function ($assignment) use ($startOfMonth, $endOfMonth) {
                            $date = Carbon::parse($assignment->created_at);
                            return $date->between($startOfMonth, $endOfMonth);
                        });
                        break;
                    case 'year':
                        $startOfYear = $now->copy()->startOfYear();
                        $endOfYear = $now->copy()->endOfYear();
                        $assignments = $assignments->filter(function ($assignment) use ($startOfYear, $endOfYear) {
                            $date = Carbon::parse($assignment->created_at);
                            return $date->between($startOfYear, $endOfYear);
                        });
                        break;
                }
            }

            // Calculate order metrics
            $totalAssignments = $assignments->count();
            $completedAssignments = $assignments->whereIn('status', ['completed', 'dispatched'])->count();
            $approvedAssignments = $assignments->whereIn('status', ['approved', 'dispatched'])->count();
            $rejectedAssignments = $assignments->where('status', 'rejected')->count();

            // Calculate quantities
            $totalAssignedQuantity = $assignments->sum('assigned_quantity');
            $totalCompletedQuantity = $assignments->whereIn('status', ['completed', 'dispatched'])->sum('completed_quantity');
            $totalApprovedQuantity = $assignments->whereIn('status', ['approved', 'dispatched'])->sum('approved_quantity');
            $totalRejectedQuantity = $assignments->where('status', 'rejected')->sum('completed_quantity');

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
                    'rejected_assignments' => $rejectedAssignments,
                    'completion_rate' => $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0,
                    'approval_rate' => $completedAssignments > 0 ? round(($approvedAssignments / $completedAssignments) * 100, 2) : 0,
                    'rejection_rate' => $completedAssignments > 0 ? round(($rejectedAssignments / $completedAssignments) * 100, 2) : 0
                ],
                'products' => [
                    'total_assigned' => $totalAssignedQuantity,
                    'total_completed' => $totalCompletedQuantity,
                    'total_approved' => $totalApprovedQuantity,
                    'total_rejected' => $totalRejectedQuantity,
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

    public function export(Request $request)
    {
        // Start with a base query
        $query = Artisan::with(['department', 'orderAssignments', 'attendances']);

        // Apply department filter if specified
        if ($request->has('department') && !empty($request->department)) {
            $query->where('department_id', $request->department);
        }

        // Get all artisans
        $artisans = $query->get();

        // Transform the data
        $transformedData = $artisans->map(function ($artisan) use ($request) {
            // Filter attendance based on date range if specified
            $attendances = $artisan->attendances;
            if ($request->has('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $attendances = $attendances->filter(function ($attendance) use ($now) {
                            return Carbon::parse($attendance->date)->format('Y-m-d') === $now->format('Y-m-d');
                        });
                        break;
                    case 'week':
                        $startOfWeek = $now->copy()->startOfWeek();
                        $endOfWeek = $now->copy()->endOfWeek();
                        $attendances = $attendances->filter(function ($attendance) use ($startOfWeek, $endOfWeek) {
                            $date = Carbon::parse($attendance->date);
                            return $date->between($startOfWeek, $endOfWeek);
                        });
                        break;
                    case 'month':
                        $startOfMonth = $now->copy()->startOfMonth();
                        $endOfMonth = $now->copy()->endOfMonth();
                        $attendances = $attendances->filter(function ($attendance) use ($startOfMonth, $endOfMonth) {
                            $date = Carbon::parse($attendance->date);
                            return $date->between($startOfMonth, $endOfMonth);
                        });
                        break;
                    case 'year':
                        $startOfYear = $now->copy()->startOfYear();
                        $endOfYear = $now->copy()->endOfYear();
                        $attendances = $attendances->filter(function ($attendance) use ($startOfYear, $endOfYear) {
                            $date = Carbon::parse($attendance->date);
                            return $date->between($startOfYear, $endOfYear);
                        });
                        break;
                }
            }

            // Calculate attendance metrics
            $totalDays = $attendances->count();
            $presentDays = $attendances->where('status', 'present')->count();
            $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;

            // Filter assignments based on date range if specified
            $assignments = $artisan->orderAssignments;
            if ($request->has('date_range')) {
                $now = Carbon::now();
                switch ($request->date_range) {
                    case 'today':
                        $assignments = $assignments->filter(function ($assignment) use ($now) {
                            return Carbon::parse($assignment->created_at)->format('Y-m-d') === $now->format('Y-m-d');
                        });
                        break;
                    case 'week':
                        $startOfWeek = $now->copy()->startOfWeek();
                        $endOfWeek = $now->copy()->endOfWeek();
                        $assignments = $assignments->filter(function ($assignment) use ($startOfWeek, $endOfWeek) {
                            $date = Carbon::parse($assignment->created_at);
                            return $date->between($startOfWeek, $endOfWeek);
                        });
                        break;
                    case 'month':
                        $startOfMonth = $now->copy()->startOfMonth();
                        $endOfMonth = $now->copy()->endOfMonth();
                        $assignments = $assignments->filter(function ($assignment) use ($startOfMonth, $endOfMonth) {
                            $date = Carbon::parse($assignment->created_at);
                            return $date->between($startOfMonth, $endOfMonth);
                        });
                        break;
                    case 'year':
                        $startOfYear = $now->copy()->startOfYear();
                        $endOfYear = $now->copy()->endOfYear();
                        $assignments = $assignments->filter(function ($assignment) use ($startOfYear, $endOfYear) {
                            $date = Carbon::parse($assignment->created_at);
                            return $date->between($startOfYear, $endOfYear);
                        });
                        break;
                }
            }

            // Calculate order metrics
            $totalAssignments = $assignments->count();
            $completedAssignments = $assignments->whereIn('status', ['completed', 'dispatched'])->count();
            $approvedAssignments = $assignments->whereIn('status', ['approved', 'dispatched'])->count();
            $rejectedAssignments = $assignments->where('status', 'rejected')->count();

            // Calculate quantities
            $totalAssignedQuantity = $assignments->sum('assigned_quantity');
            $totalCompletedQuantity = $assignments->whereIn('status', ['completed', 'dispatched'])->sum('completed_quantity');
            $totalApprovedQuantity = $assignments->whereIn('status', ['approved', 'dispatched'])->sum('approved_quantity');
            $totalRejectedQuantity = $assignments->where('status', 'rejected')->sum('completed_quantity');

            return [
                'name' => $artisan->name,
                'department' => $artisan->department->name ?? 'N/A',
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'attendance_rate' => round($attendanceRate, 2) . '%',
                'total_assignments' => $totalAssignments,
                'completed_assignments' => $completedAssignments,
                'approved_assignments' => $approvedAssignments,
                'rejected_assignments' => $rejectedAssignments,
                'completion_rate' => ($totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 2) : 0) . '%',
                'approval_rate' => ($completedAssignments > 0 ? round(($approvedAssignments / $completedAssignments) * 100, 2) : 0) . '%',
                'rejection_rate' => ($completedAssignments > 0 ? round(($rejectedAssignments / $completedAssignments) * 100, 2) : 0) . '%',
                'total_assigned' => $totalAssignedQuantity,
                'total_completed' => $totalCompletedQuantity,
                'total_approved' => $totalApprovedQuantity,
                'total_rejected' => $totalRejectedQuantity,
                'average_per_day' => $totalDays > 0 ? round($totalApprovedQuantity / $totalDays, 2) : 0
            ];
        });

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="artisan_productivity_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($transformedData) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'Artisan Name',
                'Department',
                'Total Days',
                'Present Days',
                'Attendance Rate',
                'Total Assignments',
                'Completed Assignments',
                'Approved Assignments',
                'Rejected Assignments',
                'Completion Rate',
                'Approval Rate',
                'Rejection Rate',
                'Total Assigned Products',
                'Total Completed Products',
                'Total Approved Products',
                'Total Rejected Products',
                'Average Products per Day'
            ]);

            // Add data rows
            foreach ($transformedData as $artisan) {
                fputcsv($file, [
                    $artisan['name'],
                    $artisan['department'],
                    $artisan['total_days'],
                    $artisan['present_days'],
                    $artisan['attendance_rate'],
                    $artisan['total_assignments'],
                    $artisan['completed_assignments'],
                    $artisan['approved_assignments'],
                    $artisan['rejected_assignments'],
                    $artisan['completion_rate'],
                    $artisan['approval_rate'],
                    $artisan['rejection_rate'],
                    $artisan['total_assigned'],
                    $artisan['total_completed'],
                    $artisan['total_approved'],
                    $artisan['total_rejected'],
                    $artisan['average_per_day']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
