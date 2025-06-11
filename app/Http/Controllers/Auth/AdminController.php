<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Create a new AdminController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Login admin and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // First check if user exists and is an admin
        $user = User::where(function($query) use ($request) {
            $query->where('user_name', $request->username)
                  ->orWhere('email', $request->username);
        })
        ->where('user_type', 'admin')
        ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => ['No admin account found with these credentials.'],
            ]);
        }

        // Attempt login with credentials
        $credentials = [
            'password' => $request->password,
            'user_type' => 'admin'
        ];

        // Check if the input is email or username
        if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $request->username;
        } else {
            $credentials['user_name'] = $request->username;
        }

        if (!$token = Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $user = Auth::user();

        return response()->json([
            'message' => 'Login successful',
            'authorization' => [
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout admin (Invalidate the token)
     */
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token
     */
    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorization' => [
                'token' => Auth::refresh(),
            ]
        ]);
    }

    /**
     * Get the authenticated admin
     */
    public function user()
    {
        return response()->json(Auth::user());
    }
} 