<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class EmployeeController extends Controller
{
    //
    public function show($id)
    {
        // Fetch employee details using employee_id instead of user_id
        $employee = Employee::where('id', $id)->with('user')->firstOrFail();
        return response()->json($employee);
    }
}
