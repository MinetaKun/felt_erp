<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Error;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use Error, Helpers;

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            $user = User::with('roles.permissions')
                ->where('email', $credentials['email'])
                ->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'message' => 'Account is inactive'
                ], 401);
            }

            // Regenerate the session ID to prevent session fixation
            $request->session()->regenerate();

            // Login the user and remember them
            Auth::login($user, true);

            // Update the remember token
            $user->setRememberToken(Str::random(60));
            $user->save();

            // Generate token for API access
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'permissions' => $this->extractPermissionsFromUser($user)
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $error) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $error->errors()
            ], 422);
        } catch (\Exception $error) {
            return response()->json([
                'message' => 'An error occurred during login'
            ], 500);
        }
    }

    public function verify()
    {
        if (!($id = Auth::id())) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $user = User::with('roles.permissions')->find($id);

        return response()->json($this->extractPermissionsFromUser($user));
    }

    public function logout(Request $request)
    {
        try {
            // Get the current user
            $user = Auth::user();

            // Clear the remember token
            if ($user) {
                $user->setRememberToken(null);
                $user->save();
            }

            // Revoke all tokens for the user
            $user?->tokens()->delete();

            // Logout from web guard
            Auth::guard('web')->logout();

            // Invalidate the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Clear any cached user data
            cache()->forget('user_' . Auth::id());

            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $error) {
            return $this->errorResponse($error);
        }
    }
}
