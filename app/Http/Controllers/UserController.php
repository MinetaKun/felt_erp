<?php
// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;

class UserController extends Controller
{

    public function index()
    {
        $users = User::paginate(10);
        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        // Store profile photo if exists
        $profilePhotoPath = $request->hasFile('profile_photo')
            ? $request->file('profile_photo')->store('users/photos', 'public')
            : null;

        // Create user
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'photo' => $profilePhotoPath,
            'role' => $request->role,
        ]);

        // If user is an employee, store additional details
        if ($request->role === 'employee') {
            $citizenshipPhotoPath = $request->hasFile('citizenship_photo')
                ? $request->file('citizenship_photo')->store('employees/citizenship', 'public')
                : null;

            Employee::create([
                'user_id' => $user->id,
                'employee_type' => $request->employee_type,
                'department' => $request->department,
                'join_date' => $request->join_date,
                'citizenship_no' => $request->citizenship_no,
                'pan_no' => $request->pan_no,
                'citizenship_photo' => $citizenshipPhotoPath,
                'skills' => json_encode($request->skills), // Store skills as JSON
            ]);
        }

        return response()->json(['message' => 'User created successfully'], 201);
    }
}
