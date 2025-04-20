<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;

class ArtisanController extends Controller
{
    public function index(Request $request)
    {
        $query = Artisan::with('department');

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->has('department') && !empty($request->department)) {
            $query->where('department_id', $request->department);
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

        // Pagination
        $perPage = $request->has('per_page') ? $request->per_page : 15;
        $artisans = $query->paginate($perPage);

        return response()->json($artisans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:artisans,email',
            'phone_number' => 'required|string|max:20',
            'basic_salary' => 'required|numeric',
            'pan_number' => 'required|string|max:20|unique:artisans,pan_number',
            'department_id' => 'required|exists:departments,id',
        ]);

        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('photos', 'public');
        }

        if ($request->hasFile('citizenship_photo')) {
            $validated['citizenship_photo'] = $request->file('citizenship_photo')->store('photos', 'public');
        }

        $artisan = Artisan::create($validated);
        return response()->json($artisan, 201);
    }

    public function show($id)
    {
        $artisan = Artisan::with('department')->find($id);

        if (!$artisan) {
            return response()->json([
                'success' => false,
                'message' => 'Artisan not found',
            ], 404);
        }

        $response = [
            'id' => $artisan->id,
            'name' => $artisan->name,
            'email' => $artisan->email,
            'phone_number' => $artisan->phone_number,
            'basic_salary' => $artisan->basic_salary,
            'pan_number' => $artisan->pan_number,
            'department' => $artisan->department,
            'profile_photo_url' => $artisan->profile_photo ? asset('storage/' . $artisan->profile_photo) : null,
            'citizenship_photo_url' => $artisan->citizenship_photo ? asset('storage/' . $artisan->citizenship_photo) : null,
            'created_at' => $artisan->created_at,
            'updated_at' => $artisan->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function update(Request $request, $id)
    {
        $artisan = Artisan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:artisans,email,' . $artisan->id,
            'phone_number' => 'sometimes|required|string|max:20',
            'basic_salary' => 'sometimes|required|numeric',
            'pan_number' => 'sometimes|required|string|max:20|unique:artisans,pan_number,' . $artisan->id,
            'department_id' => 'sometimes|required|exists:departments,id',
            'profile_photo' => 'sometimes|image|max:2048',
            'citizenship_photo' => 'sometimes|image|max:2048'
        ]);

        foreach (['profile_photo', 'citizenship_photo'] as $fileField) {
            if ($request->hasFile($fileField)) {
                if ($artisan->$fileField) {
                    Storage::disk('public')->delete($artisan->$fileField);
                }
                $validated[$fileField] = $request->file($fileField)->store('photos', 'public');
            }
        }

        $artisan->update($validated);

        return response()->json([
            'success' => true,
            'data' => $artisan->fresh(),
            'message' => 'Artisan updated successfully'
        ]);
    }

    public function destroy($artisanId)
    {
        $artisan = Artisan::find($artisanId);
        $artisan->delete();
        return response()->json(['message' => 'Artisan deleted']);
    }

    public function attendance($id)
    {
        $artisan = Artisan::findOrFail($id);
        $attendance = $artisan->attendance;
        return response()->json($attendance);
    }

    public function export(Request $request)
    {
        $query = Artisan::with('department');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('department') && !empty($request->department)) {
            $query->where('department_id', $request->department);
        }

        if ($request->has('sort')) {
            [$column, $direction] = explode(':', $request->sort);
            $query->orderBy($column, $direction);
        } else {
            $query->latest();
        }

        $artisans = $query->get();

        $csv = Writer::createFromFileObject(new SplTempFileObject());

        $csv->insertOne([
            'ID',
            'Name',
            'Email',
            'Phone Number',
            'PAN Number',
            'Basic Salary',
            'Department',
            'Profile Photo URL',
            'Citizenship Photo URL',
            'Join Date',
            'Updated At'
        ]);

        foreach ($artisans as $artisan) {
            $csv->insertOne([
                $artisan->id,
                $artisan->name,
                $artisan->email,
                $artisan->phone_number,
                $artisan->pan_number,
                $artisan->basic_salary,
                $artisan->department ? $artisan->department->name : '',
                $artisan->profile_photo ? asset('storage/' . $artisan->profile_photo) : '',
                $artisan->citizenship_photo ? asset('storage/' . $artisan->citizenship_photo) : '',
                $artisan->created_at->format('Y-m-d H:i:s'),
                $artisan->updated_at->format('Y-m-d H:i:s')
            ]);
        }

        $filename = 'artisans_export_' . date('Y-m-d_H-i-s') . '.csv';

        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
