<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Traits\Error;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use Error, Helpers;

    public function index(Request $request)
    {
        $users = User::with(['roles.permissions', 'department'])->get();
        $users = $users->map(
            fn($user) => $this->extractPermissionsFromUser($user)
        );

        // Add the full profile photo URL to each user
        $users = $users->map(function ($user) {
            if ($user->profile_photo) {
                $user->profile_photo_url = Storage::url($user->profile_photo);
            } else {
                $user->profile_photo_url = null;
            }
            return $user;
        });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'phone_number' => ['required', 'digits:10'],
                'profile_photo' => ['nullable', 'image', 'max:2048'],
                'password' => ['required', 'string', 'min:8'],
                'roles' => ['required', 'array'],
            ]);

            $validated['password'] = Hash::make($validated['password']);

            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $validated['profile_photo'] = $path;
            }

            $user = new User();
            foreach ($validated as $key => $val) {
                if ($key !== 'roles') {
                    $user->{$key} = $val;
                }
            }
            $user->save();

            if (isset($request->roles) && is_array($request->roles)) {
                $superAdminRole = Role::where('name', 'super-admin')->first();
                $roles = array_filter(
                    $request->roles,
                    fn($roleId) => $roleId !== ($superAdminRole->id ?? null)
                );
                $user->roles()->sync($roles);
            }

            $user->load('roles.permissions');
            $user = $this->extractPermissionsFromUser($user);

            // Add the full profile photo URL to the response
            if ($user->profile_photo) {
                $user->profile_photo_url = Storage::url($user->profile_photo);
            } else {
                $user->profile_photo_url = null;
            }

            return response()->json($user, 201);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'email' => [
                    'sometimes',
                    'required',
                    'email',
                    Rule::unique('users')->ignore($request->userId),
                ],
                'phone_number' => ['sometimes', 'required', 'digits:10'],
                'profile_photo' => ['nullable', 'image', 'max:2048'],
                'password' => ['sometimes', 'string', 'min:8'],
                'roles' => ['sometimes', 'required', 'array'],
                'department_id' => ['nullable', 'exists:departments,id'],
            ]);

            $user = User::with('roles')->find($request->userId);
            if (!$user) {
                throw new \Exception('Error|User not found--404', 13333);
            }

            $usersSuperAdminRole = $user->roles->firstWhere('name', 'super-admin');
            if ($usersSuperAdminRole) {
                throw new \Exception(
                    'Error|Super admin user can\'t be updated--401',
                    13333
                );
            }

            if ($request->hasFile('profile_photo')) {
                if ($user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                $path = $request->file('profile_photo')->store('profile_photos', 'public');
                $validated['profile_photo'] = $path;
            } else {
                unset($validated['profile_photo']);
            }

            if (isset($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            }

            foreach ($validated as $key => $val) {
                if ($key !== 'roles') {
                    $user->{$key} = $val;
                }
            }
            $user->save();

            if (isset($request->roles) && is_array($request->roles)) {
                $superAdminRole = Role::where('name', 'super-admin')->first();
                $roles = array_filter(
                    $request->roles,
                    fn($roleId) => $roleId !== ($superAdminRole->id ?? null)
                );
                $user->roles()->sync($roles);
            }

            $user->load(['roles.permissions', 'department']);
            $user = $this->extractPermissionsFromUser($user);

            // Add the full profile photo URL to the response
            if ($user->profile_photo) {
                $user->profile_photo_url = Storage::url($user->profile_photo);
            } else {
                $user->profile_photo_url = null;
            }

            return response()->json($user);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $user = User::with('roles')->find($request->userId);
            if (!$user) {
                throw new \Exception('Error|User not found--404', 13333);
            }

            $superAdminRole = $user->roles->firstWhere('name', 'super-admin');
            if ($superAdminRole) {
                throw new \Exception(
                    'Error|Super admin user can\'t be deleted--401',
                    13333
                );
            }

            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $user->delete();

            return response()->json([], 204);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }
}
