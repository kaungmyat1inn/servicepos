<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Central\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login super admin
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $credentials['email'])->first();

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($admin->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account is not active.'],
            ]);
        }

        // Update last login
        $admin->update(['last_login_at' => now()]);

        // Generate simple API token
        $token = Str::random(80);
        $admin->api_token = hash('sha256', $token);
        $admin->save();

        return response()->json([
            'message' => 'Login successful',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout current admin
     */
    public function logout(Request $request)
    {
        $admin = $request->user();
        $admin->api_token = null;
        $admin->save();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get current admin info
     */
    public function me(Request $request)
    {
        return response()->json([
            'admin' => $request->user(),
        ]);
    }
}

