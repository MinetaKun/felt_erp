<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index($artisanId)
    {
        $attendance = Attendance::where('artisan_id', $artisanId)->get();
        return response()->json($attendance);
    }

    public function store(Request $request, $artisanId)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:Present,Absent,Late',
        ]);

        $attendance = new Attendance($validated);
        $attendance->artisan_id = $artisanId;
        $attendance->save();
        return response()->json($attendance, 201);
    }
}
