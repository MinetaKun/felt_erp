<?php

namespace App\Http\Controllers;

use App\Models\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Department;
use League\Csv\Reader;
use League\Csv\Writer;
use SplTempFileObject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;



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

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
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

        // Transform the response to include skills
        $artisans->getCollection()->transform(function ($artisan) {
            return [
                'id' => $artisan->id,
                'name' => $artisan->name,
                'email' => $artisan->email,
                'phone_number' => $artisan->phone_number,
                'basic_salary' => $artisan->basic_salary,
                'pan_number' => $artisan->pan_number,
                'department' => $artisan->department,
                'status' => $artisan->status,
                'skills' => is_string($artisan->skills) ? json_decode($artisan->skills, true) : ($artisan->skills ?? []),
                'profile_photo_url' => $artisan->profile_photo, // Return Cloudinary URL directly
                'citizenship_photo_url' => $artisan->citizenship_photo, // Return Cloudinary URL direct
                'created_at' => $artisan->created_at,
                'updated_at' => $artisan->updated_at,
            ];
        });

        return response()->json($artisans);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Artisan store method called', [
            'request_method' => $request->method(),
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:artisans,email',
            'phone_number' => 'required|string|max:20',
            'basic_salary' => 'required|numeric|min:0',
            'pan_number' => 'required|string|max:20|unique:artisans,pan_number',
            'bank_account_number' => 'required|string|max:50|unique:artisans,bank_account_number',
            'department_id' => 'required|exists:departments,id',
            'status' => 'sometimes|in:active,inactive',
            'skills' => 'required|string',
            'profile_photo' => 'sometimes|image|max:2048',
            'citizenship_photo' => 'sometimes|image|max:2048',
        ]);

        \Illuminate\Support\Facades\Log::info('Validation passed', ['validated_data' => $validated]);

        // Set default status if not provided
        if (!isset($validated['status'])) {
            $validated['status'] = 'inactive';
        }

        // Decode the skills JSON string
        $validated['skills'] = json_decode($validated['skills'], true);

        // Upload profile photo to Cloudinary
        if ($request->hasFile('profile_photo')) {
            $cloudinary = new Cloudinary();
            $uploadedFile = $cloudinary->uploadApi()->upload(
                $request->file('profile_photo')->getRealPath(),
                ['folder' => 'artisans/profile-photos']
            );
            $validated['profile_photo'] = $uploadedFile['secure_url']; // HTTPS URL
        }

        // Upload citizenship photo to Cloudinary
        if ($request->hasFile('citizenship_photo')) {
            $cloudinary = isset($cloudinary) ? $cloudinary : new Cloudinary();
            $uploadedFile = $cloudinary->uploadApi()->upload(
                $request->file('citizenship_photo')->getRealPath(),
                ['folder' => 'artisans/citizenship-photos']
            );
            $validated['citizenship_photo'] = $uploadedFile['secure_url'];
        }

        $artisan = Artisan::create($validated);

        \Illuminate\Support\Facades\Log::info('Artisan created', ['artisan' => $artisan]);

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
            'bank_account_number' => $artisan->bank_account_number,
            'department' => $artisan->department,
            'status' => $artisan->status,
            'skills' => is_string($artisan->skills) ? json_decode($artisan->skills, true) : ($artisan->skills ?? []),
            'profile_photo_url' => $artisan->profile_photo, // Return Cloudinary URL directly
            'citizenship_photo_url' => $artisan->citizenship_photo, // Return Cloudinary URL direct
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

        \Illuminate\Support\Facades\Log::info('Updating artisan', [
            'id' => $id,
            'request_data' => $request->all()
        ]);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:artisans,email,' . $artisan->id,
            'phone_number' => 'sometimes|required|string|max:20',
            'basic_salary' => 'sometimes|required|numeric|min:0',
            'pan_number' => 'sometimes|required|string|max:20|unique:artisans,pan_number,' . $artisan->id,
            'bank_account_number' => 'sometimes|required|string|max:50|unique:artisans,bank_account_number,' . $artisan->id,
            'department_id' => 'sometimes|required|exists:departments,id',
            'status' => 'sometimes|in:active,inactive',
            'skills' => 'sometimes|required|string',
            'profile_photo' => 'sometimes|image|max:2048',
            'citizenship_photo' => 'sometimes|image|max:2048',
        ]);

        \Illuminate\Support\Facades\Log::info('Validated data', ['validated' => $validated]);

        // Decode the skills JSON string
        if (isset($validated['skills'])) {
            $validated['skills'] = json_decode($validated['skills'], true);
        }

        // Upload profile photo to Cloudinary
        if ($request->hasFile('profile_photo')) {
            $cloudinary = new Cloudinary();
            $uploadedFile = $cloudinary->uploadApi()->upload(
                $request->file('profile_photo')->getRealPath(),
                ['folder' => 'artisans/profile-photos']
            );
            $validated['profile_photo'] = $uploadedFile['secure_url']; // HTTPS URL
        }

        // Upload citizenship photo to Cloudinary
        if ($request->hasFile('citizenship_photo')) {
            $cloudinary = isset($cloudinary) ? $cloudinary : new Cloudinary();
            $uploadedFile = $cloudinary->uploadApi()->upload(
                $request->file('citizenship_photo')->getRealPath(),
                ['folder' => 'artisans/citizenship-photos']
            );
            $validated['citizenship_photo'] = $uploadedFile['secure_url'];
        }

        // Update the artisan with the validated data
        $artisan->fill($validated);
        $artisan->save();

        \Illuminate\Support\Facades\Log::info('Artisan updated', [
            'id' => $artisan->id,
            'updated_data' => $artisan->fresh()->toArray()
        ]);

        // Refresh the artisan to get updated data
        $artisan = $artisan->fresh();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $artisan->id,
                'name' => $artisan->name,
                'email' => $artisan->email,
                'phone_number' => $artisan->phone_number,
                'basic_salary' => $artisan->basic_salary,
                'pan_number' => $artisan->pan_number,
                'bank_account_number' => $artisan->bank_account_number,
                'department' => $artisan->department,
                'status' => $artisan->status,
                'skills' => $artisan->skills,
                'profile_photo_url' => $artisan->profile_photo, // Return Cloudinary URL directly
                'citizenship_photo_url' => $artisan->citizenship_photo, // Return Cloudinary URL directly
                'created_at' => $artisan->created_at,
                'updated_at' => $artisan->updated_at,
            ],
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

    public function updateStatus($id)
    {
        $artisan = Artisan::findOrFail($id);
        $status = $artisan->updateStatus();

        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => 'Artisan status updated successfully'
        ]);
    }

    public function export(Request $request)
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

        // Status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
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

        try {
            $artisans = $query->get();

            if ($artisans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No artisans found to export'
                ], 404);
            }

            $csv = Writer::createFromFileObject(new SplTempFileObject());

            $csv->insertOne([
                'ID',
                'Name',
                'Email',
                'Phone Number',
                'PAN Number',
                'Basic Salary',
                'Department',
                'Status',
                'Join Date',
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
                    $artisan->status,
                    $artisan->created_at->format('Y-m-d'),
                ]);
            }

            $filename = 'artisans_export_' . date('Y-m-d_H-i-s') . '.csv';
            $csvContent = (string) $csv;

            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($csvContent));
        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating CSV: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        // Determine file type and process accordingly
        $fileExtension = $file->getClientOriginalExtension();

        if (in_array($fileExtension, ['xlsx', 'xls'])) {
            // Process Excel file
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $spreadsheet = $reader->load($path);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
        } else {
            // Process CSV file
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);
            $rows = iterator_to_array($csv->getRecords());
        }

        // Skip header row
        $successCount = 0;
        $errors = [];

        // Begin transaction
        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                // Skip header row if processing Excel
                if ($index === 0 && in_array($fileExtension, ['xlsx', 'xls'])) {
                    continue;
                }

                // Map CSV columns to database fields
                $data = [];

                // For Excel files, use numeric indices
                if (in_array($fileExtension, ['xlsx', 'xls'])) {
                    $data = [
                        'name' => $row[1] ?? null,
                        'email' => $row[2] ?? null,
                        'phone_number' => $row[3] ?? null,
                        'basic_salary' => $row[5] ?? 0,
                        'pan_number' => $row[4] ?? null,
                        'department_id' => null,
                        'status' => $row[7] ?? 'inactive',
                    ];

                    // Find department by name
                    if (!empty($row[6])) {
                        $department = Department::where('name', $row[6])->first();
                        if ($department) {
                            $data['department_id'] = $department->id;
                        } else {
                            $errors[] = "Row " . ($index + 1) . ": Department '{$row[6]}' not found";
                            continue;
                        }
                    } else {
                        $errors[] = "Row " . ($index + 1) . ": Department is required";
                        continue;
                    }
                } else {
                    // For CSV files, use associative array
                    $data = [
                        'name' => $row['Name'] ?? null,
                        'email' => $row['Email'] ?? null,
                        'phone_number' => $row['Phone Number'] ?? null,
                        'basic_salary' => $row['Basic Salary'] ?? 0,
                        'pan_number' => $row['PAN Number'] ?? null,
                        'department_id' => null,
                        'status' => $row['Status'] ?? 'inactive',
                    ];

                    // Find department by name
                    if (!empty($row['Department'])) {
                        $department = Department::where('name', $row['Department'])->first();
                        if ($department) {
                            $data['department_id'] = $department->id;
                        } else {
                            $errors[] = "Row " . ($index + 1) . ": Department '{$row['Department']}' not found";
                            continue;
                        }
                    } else {
                        $errors[] = "Row " . ($index + 1) . ": Department is required";
                        continue;
                    }
                }

                // Validate required fields
                if (empty($data['name']) || empty($data['email']) || empty($data['phone_number']) || empty($data['pan_number'])) {
                    $errors[] = "Row " . ($index + 1) . ": Missing required fields";
                    continue;
                }

                // Check if email already exists
                if (Artisan::where('email', $data['email'])->exists()) {
                    $errors[] = "Row " . ($index + 1) . ": Email '{$data['email']}' already exists";
                    continue;
                }

                // Check if PAN number already exists
                if (Artisan::where('pan_number', $data['pan_number'])->exists()) {
                    $errors[] = "Row " . ($index + 1) . ": PAN Number '{$data['pan_number']}' already exists";
                    continue;
                }

                // Validate status
                if (!in_array($data['status'], ['active', 'inactive'])) {
                    $data['status'] = 'inactive'; // Default to inactive if invalid
                }

                // Create artisan
                Artisan::create($data);
                $successCount++;
            }

            // Commit transaction if no errors or if some records were successful
            if (empty($errors) || $successCount > 0) {
                DB::commit();
                $message = $successCount . " artisans imported successfully";
                if (!empty($errors)) {
                    $message .= " with some errors";
                }
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $errors
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'No artisans were imported due to errors',
                    'errors' => $errors
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'An error occurred during import: ' . $e->getMessage()
            ], 500);
        }
    }
}
