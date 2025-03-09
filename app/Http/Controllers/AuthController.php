<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'email' => 'required|email',
        ]);

        // Get the credentials from the request
        $credentials = $request->only('email', 'password');

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // If passwords match, log the user in
            Auth::login($user);

            return response()->json([
                'message' => 'Login successful',
                'user' => Auth::user(),
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }


        // if ($user && $user->password === $credentials['password']) {
        //     // If passwords match (plain text), log the user in
        //     Auth::login($user);

        //     return response()->json([
        //         'message' => 'Login successful',
        //         'user' => Auth::user(),
        //     ]);
        // }

        // Return error if credentials do not match
        // return response()->json(['message' => 'Invalid credentials'], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
