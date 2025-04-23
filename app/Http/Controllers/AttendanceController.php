<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use League\Csv\Writer;
use SplTempFileObject;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['attendanceable' => function ($query) {
            $query->when($query->getModel() instanceof Artisan, function ($q) {
                $q->with('department');
            });
        }]);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('type')) {
            $query->where('attendanceable_type', $request->type === 'user' ? User::class : Artisan::class);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->whereHasMorph('attendanceable', [Artisan::class], function ($query) use ($request) {
                $query->where('department_id', $request->department_id);
            });
        }

        $attendances = $query->get();

        $data = $attendances->map(function ($attendance) {
            $attendanceable = $attendance->attendanceable;
            return [
                'id' => $attendance->id,
                'name' => $attendanceable->name,
                'type' => $attendanceable instanceof User ? 'user' : 'artisan',
                'date' => $attendance->date,
                'check_in' => $attendance->check_in,
                'check_out' => $attendance->check_out,
                'status' => $attendance->status,
                'department' => $attendanceable instanceof Artisan ? ($attendanceable->department?->name ?? 'N/A') : 'N/A',
                'remarks' => $attendance->remarks
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'remarks' => 'nullable|string|max:255',
            'attendanceable_id' => 'required|integer',
            'attendanceable_type' => 'required|in:App\\Models\\User,App\\Models\\Artisan'
        ]);

        // Check if attendance already exists for this date and person
        $existingAttendance = Attendance::where('date', $request->date)
            ->where('attendanceable_id', $request->attendanceable_id)
            ->where('attendanceable_type', $request->attendanceable_type)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Attendance record already exists for this date and person.'
            ], 422);
        }

        $attendance = Attendance::create($request->all());

        return response()->json([
            'message' => 'Attendance recorded successfully.',
            'data' => $attendance
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['attendanceable' => function ($query) {
            $query->with('department');
        }])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $attendance->id,
                'name' => $attendance->attendanceable->name,
                'type' => class_basename($attendance->attendanceable_type),
                'department' => $attendance->attendanceable->department->name ?? 'N/A',
                'date' => $attendance->date,
                'status' => $attendance->status,
                'remarks' => $attendance->remarks,
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'remarks' => 'nullable|string|max:255'
        ]);

        $attendance->update($request->all());

        return response()->json([
            'message' => 'Attendance updated successfully.',
            'data' => $attendance
        ]);
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json([
            'message' => 'Attendance deleted successfully.'
        ]);
    }

    public function dailyReport(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');

        $query = Attendance::with(['attendanceable' => function ($query) {
            $query->with('department');
        }])->where('date', $date);

        if ($request->has('department')) {
            $query->whereHas('attendanceable', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        $attendances = $query->get();

        $report = [
            'date' => $date,
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'details' => $attendances->map(function ($attendance) {
                return [
                    'name' => $attendance->attendanceable->name,
                    'type' => class_basename($attendance->attendanceable_type),
                    'department' => $attendance->attendanceable->department->name ?? 'N/A',
                    'status' => $attendance->status,
                    'remarks' => $attendance->remarks,
                ];
            })
        ];

        return response()->json($report);
    }

    public function monthlyReport(Request $request)
    {
        $year = $request->year ?? Carbon::now()->year;
        $month = $request->month ?? Carbon::now()->month;

        $query = Attendance::with(['attendanceable' => function ($query) {
            $query->with('department');
        }])->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($request->has('department')) {
            $query->whereHas('attendanceable', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        $attendances = $query->get();

        $report = [
            'month' => Carbon::createFromDate($year, $month)->format('F Y'),
            'total' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'details' => $attendances->groupBy('attendanceable_id')->map(function ($records) {
                $firstRecord = $records->first();
                return [
                    'name' => $firstRecord->attendanceable->name,
                    'type' => class_basename($firstRecord->attendanceable_type),
                    'department' => $firstRecord->attendanceable->department->name ?? 'N/A',
                    'present_days' => $records->where('status', 'present')->count(),
                    'absent_days' => $records->where('status', 'absent')->count(),
                    'late_days' => $records->where('status', 'late')->count(),
                ];
            })->values()
        ];

        return response()->json($report);
    }

    public function export(Request $request)
    {
        $query = Attendance::with(['attendanceable' => function ($query) {
            $query->with('department');
        }]);

        // Apply filters
        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->has('type')) {
            $query->where('attendanceable_type', $request->type === 'user' ? User::class : Artisan::class);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('department')) {
            $query->whereHas('attendanceable', function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        $data = $attendances->map(function ($attendance) {
            return [
                'Name' => $attendance->attendanceable->name,
                'Type' => class_basename($attendance->attendanceable_type),
                'Department' => $attendance->attendanceable->department->name ?? 'N/A',
                'Date' => $attendance->date,
                'Status' => ucfirst($attendance->status),
                'Remarks' => $attendance->remarks,
            ];
        });

        return response()->json($data);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.date' => 'required|date',
            'records.*.status' => 'required|in:present,absent,late',
            'records.*.remarks' => 'nullable|string|max:255',
            'records.*.attendanceable_id' => 'required|integer',
            'records.*.attendanceable_type' => 'required|in:App\\Models\\User,App\\Models\\Artisan'
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->records as $record) {
                // Check if attendance exists for this specific person and date
                $existingAttendance = Attendance::where('date', $record['date'])
                    ->where('attendanceable_id', $record['attendanceable_id'])
                    ->where('attendanceable_type', $record['attendanceable_type'])
                    ->first();

                if ($existingAttendance) {
                    // Update existing record
                    $existingAttendance->update([
                        'status' => $record['status'],
                        'remarks' => $record['remarks'] ?? '',
                    ]);
                } else {
                    // Create new record
                    Attendance::create([
                        'date' => $record['date'],
                        'status' => $record['status'],
                        'remarks' => $record['remarks'] ?? '',
                        'attendanceable_id' => $record['attendanceable_id'],
                        'attendanceable_type' => $record['attendanceable_type'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Attendance records saved successfully.',
                'count' => count($request->records)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to save attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkDate($date)
    {
        $exists = Attendance::whereDate('date', $date)->exists();

        return response()->json([
            'exists' => $exists,
            'date' => $date
        ]);
    }

    public function getByDate($date)
    {
        $records = Attendance::with(['attendanceable' => function ($query) {
            $query->when($query->getModel() instanceof Artisan, function ($q) {
                $q->with('department');
            });
        }])
            ->whereDate('date', $date)
            ->get()
            ->map(function ($attendance) {
                $attendanceable = $attendance->attendanceable;
                return [
                    'id' => $attendance->id,
                    'name' => $attendanceable->name,
                    'type' => $attendanceable instanceof User ? 'user' : 'artisan',
                    'department' => $attendanceable instanceof Artisan ? ($attendanceable->department?->name ?? 'N/A') : 'N/A',
                    'status' => $attendance->status,
                    'remarks' => $attendance->remarks,
                ];
            });

        return response()->json($records);
    }

    public function updateByDate(Request $request, $date)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.id' => 'required|integer|exists:attendance,id',
            'records.*.status' => 'required|in:present,absent,late',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->records as $record) {
                Attendance::where('id', $record['id'])
                    ->whereDate('date', $date)
                    ->update([
                        'status' => $record['status'],
                        'remarks' => $record['remarks'] ?? '',
                    ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Attendance records updated successfully.',
                'count' => count($request->records)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update attendance records.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function report(Request $request)
    {
        $query = Attendance::with(['attendanceable.department']);

        // Apply filters
        if ($request->filled('startDate')) {
            $query->whereDate('date', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $query->whereDate('date', '<=', $request->endDate);
        }
        if ($request->filled('type')) {
            $query->where('attendanceable_type', $request->type === 'user' ? User::class : Artisan::class);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('department')) {
            $query->whereHasMorph('attendanceable', [Artisan::class], function ($q) use ($request) {
                $q->where('department_id', $request->department);
            });
        }

        // Calculate statistics
        $statistics = [
            'total' => $query->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
        ];

        // If export is requested
        if ($request->boolean('export')) {
            return $this->exportReport($query);
        }

        // Get paginated results
        $attendance = $query->latest()
            ->paginate($request->input('per_page', 15));

        // Transform the data for the response
        $attendance->getCollection()->transform(function ($record) {
            return [
                'id' => $record->id,
                'name' => $record->attendanceable->name,
                'type' => $record->attendanceable_type === User::class ? 'User' : 'Artisan',
                'department' => $record->attendanceable->department?->name ?? 'N/A',
                'date' => $record->date,
                'status' => $record->status,
                'remarks' => $record->remarks,
            ];
        });

        return response()->json([
            'data' => $attendance->items(),
            'current_page' => $attendance->currentPage(),
            'last_page' => $attendance->lastPage(),
            'from' => $attendance->firstItem(),
            'to' => $attendance->lastItem(),
            'total' => $attendance->total(),
            'statistics' => $statistics,
        ]);
    }

    private function exportReport($query)
    {
        $attendance = $query->latest()->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());

        // Add headers
        $csv->insertOne([
            'Name',
            'Type',
            'Department',
            'Date',
            'Status',
            'Check In',
            'Check Out',
            'Remarks'
        ]);

        // Add data rows
        foreach ($attendance as $record) {
            $csv->insertOne([
                $record->attendanceable->name,
                $record->attendanceable_type === User::class ? 'User' : 'Artisan',
                $record->attendanceable->department?->name ?? 'N/A',
                $record->date->format('Y-m-d'),
                ucfirst($record->status),
                $record->check_in?->format('H:i:s') ?? 'N/A',
                $record->check_out?->format('H:i:s') ?? 'N/A',
                $record->remarks ?? 'N/A'
            ]);
        }

        $filename = 'attendance_report_' . date('Y-m-d_H-i-s') . '.csv';
        $csvContent = (string) $csv;

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($csvContent));
    }
}
