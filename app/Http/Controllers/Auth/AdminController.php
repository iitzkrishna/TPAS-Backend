<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Http\JsonResponse;

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
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        // Check if request body is empty
        if (empty($request->all())) {
            return response()->json([
                'message' => 'Request body is required.',
                'errors' => [
                    'body' => ['The request body cannot be empty.']
                ]
            ], 400);
        }

        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ], [
                'username.required' => 'The username field is required.',
                'username.string' => 'The username must be a string.',
                'password.required' => 'The password field is required.',
                'password.string' => 'The password must be a string.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        // First check if user exists and is an admin
        $user = User::where(function($query) use ($request) {
            $query->where('user_name', $request->username)
                  ->orWhere('email', $request->username);
        })
        ->where('user_type', 'admin')
        ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication failed.',
                'errors' => [
                    'credentials' => ['Invalid credentials provided.']
                ]
            ], 401);
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
            return response()->json([
                'message' => 'Authentication failed.',
                'errors' => [
                    'credentials' => ['Invalid credentials provided.']
                ]
            ], 401);
        }

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'token' => $token
            ]
        ], 200);
    }

    /**
     * Logout admin (Invalidate the token)
     * 
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            Auth::logout();
            return response()->json([
                'message' => 'Successfully logged out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed.',
                'errors' => [
                    'general' => ['An error occurred while logging out. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Refresh a token
     * 
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        try {
            return response()->json([
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => Auth::refresh()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token refresh failed.',
                'errors' => [
                    'general' => ['An error occurred while refreshing the token. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Get the authenticated admin
     * 
     * @return JsonResponse
     */
    public function user(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found.',
                    'errors' => [
                        'auth' => ['No authenticated user found.']
                    ]
                ], 404);
            }

            // Only return necessary user data
            $userData = [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name
            ];

            return response()->json([
                'message' => 'User data retrieved successfully',
                'data' => [
                    'user' => $userData
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user data.',
                'errors' => [
                    'general' => ['An error occurred while retrieving user data. Please try again.']
                ]
            ], 500);
        }
    }
} 