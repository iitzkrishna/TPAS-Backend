<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TouristsController extends Controller
{
    /**
     * Create a new TouristsController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'createPassword']]);
    }

    /**
     * Register a new tourist
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
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
                'user_name' => 'required|string|max:50|unique:users',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8|confirmed',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'nationality' => 'required|string|max:50',
                'phone_number' => 'required|string|max:20',
                'gender' => 'required|in:male,female,other',
                'date_of_birth' => 'required|date',
            ], [
                'user_name.required' => 'The username field is required.',
                'user_name.string' => 'The username must be a string.',
                'user_name.max' => 'The username cannot exceed 50 characters.',
                'user_name.unique' => 'This username is already taken.',
                'email.required' => 'The email field is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email is already registered.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
                'first_name.required' => 'The first name field is required.',
                'last_name.required' => 'The last name field is required.',
                'nationality.required' => 'The nationality field is required.',
                'phone_number.required' => 'The phone number field is required.',
                'gender.required' => 'The gender field is required.',
                'gender.in' => 'The gender must be male, female, or other.',
                'date_of_birth.required' => 'The date of birth field is required.',
                'date_of_birth.date' => 'Please provide a valid date of birth.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'user_name' => $request->user_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'tourist',
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'nationality' => $request->nationality,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'is_verified' => false,
                'email_verification_token' => Str::random(64),
                'email_verification_token_expires_at' => Carbon::now()->addHours(24),
            ]);

            // Create tourist record
            $user->tourist()->create();

            // Send verification email
            Mail::send('emails.verify-email', ['token' => $user->email_verification_token], function($message) use($user) {
                $message->to($user->email);
                $message->subject('Verify Email Address');
            });

            $token = Auth::login($user);

            return response()->json([
                'message' => 'Registration successful. Please verify your email.',
                'data' => [
                    'authorization' => [
                        'token' => $token
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Registration failed.',
                'errors' => [
                    'general' => ['An error occurred while creating your account. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Login tourist and create token
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

        // First check if user exists and is a tourist
        $user = User::where(function($query) use ($request) {
            $query->where('user_name', $request->username)
                  ->orWhere('email', $request->username);
        })
        ->where('user_type', 'tourist')
        ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Authentication failed.',
                'errors' => [
                    'credentials' => ['Invalid credentials provided.']
                ]
            ], 401);
        }

        // Check if user is verified
        if (!$user->is_verified) {
            return response()->json([
                'message' => 'Please verify your email before logging in.',
                'errors' => [
                    'verification' => ['Please verify your email before logging in.']
                ]
            ], 403);
        }

        // Attempt login with credentials
        $credentials = [
            'password' => $request->password,
            'user_type' => 'tourist'
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
     * Logout tourist (Invalidate the token)
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
     * Get the authenticated tourist
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

            $user->load('tourist');

            // Only return necessary user data
            $userData = [
                'id' => $user->id,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'nationality' => $user->nationality,
                'phone_number' => $user->phone_number,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth,
                'tourist' => $user->tourist
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

    /**
     * Update tourist profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
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
            $user = Auth::user();

            $request->validate([
                'user_name' => 'sometimes|string|max:50|unique:users,user_name,' . $user->id,
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'first_name' => 'sometimes|string|max:50',
                'last_name' => 'sometimes|string|max:50',
                'nationality' => 'sometimes|string|max:50',
                'phone_number' => 'sometimes|string|max:20',
                'gender' => 'sometimes|in:male,female,other',
                'date_of_birth' => 'sometimes|date',
            ], [
                'user_name.string' => 'The username must be a string.',
                'user_name.max' => 'The username cannot exceed 50 characters.',
                'user_name.unique' => 'This username is already taken.',
                'email.email' => 'Please provide a valid email address.',
                'email.unique' => 'This email is already registered.',
                'first_name.string' => 'The first name must be a string.',
                'last_name.string' => 'The last name must be a string.',
                'nationality.string' => 'The nationality must be a string.',
                'phone_number.string' => 'The phone number must be a string.',
                'gender.in' => 'The gender must be male, female, or other.',
                'date_of_birth.date' => 'Please provide a valid date of birth.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $user->update($request->only([
                'user_name',
                'email',
                'first_name',
                'last_name',
                'nationality',
                'phone_number',
                'gender',
                'date_of_birth',
            ]));

            return response()->json([
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $user->load('tourist')
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Profile update failed.',
                'errors' => [
                    'general' => ['An error occurred while updating your profile. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Verify user's email
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
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
                'token' => 'required|string'
            ], [
                'token.required' => 'The verification token is required.',
                'token.string' => 'The verification token must be a string.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::where('email_verification_token', $request->token)
            ->where('email_verification_token_expires_at', '>', Carbon::now())
            ->where('user_type', 'tourist')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid or expired verification token.',
                'errors' => [
                    'token' => ['Invalid or expired verification token.']
                ]
            ], 400);
        }

        try {
            $user->update([
                'is_verified' => true,
                'email_verification_token' => null,
                'email_verification_token_expires_at' => null,
                'email_verified_at' => Carbon::now()
            ]);

            return response()->json([
                'message' => 'Email verified successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Email verification failed.',
                'errors' => [
                    'general' => ['An error occurred while verifying your email. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Send password reset link
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
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
                'email' => 'required|email|exists:users,email'
            ], [
                'email.required' => 'The email field is required.',
                'email.email' => 'Please provide a valid email address.',
                'email.exists' => 'No account found with this email address.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
            ->where('user_type', 'tourist')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'No tourist account found with this email.',
                'errors' => [
                    'email' => ['No tourist account found with this email.']
                ]
            ], 404);
        }

        try {
            $token = Str::random(64);
            $expiresAt = Carbon::now()->addHours(24);

            $user->update([
                'password_reset_token' => $token,
                'password_reset_token_expires_at' => $expiresAt
            ]);

            // Send email with reset link
            Mail::send('emails.reset-password', ['token' => $token], function($message) use($user) {
                $message->to($user->email);
                $message->subject('Reset Password Notification');
            });

            return response()->json([
                'message' => 'Password reset link sent to your email'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send password reset link.',
                'errors' => [
                    'general' => ['An error occurred while sending the password reset link. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Reset password
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
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
                'token' => 'required|string',
                'password' => 'required|min:8|confirmed'
            ], [
                'token.required' => 'The reset token is required.',
                'token.string' => 'The reset token must be a string.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::where('password_reset_token', $request->token)
            ->where('password_reset_token_expires_at', '>', Carbon::now())
            ->where('user_type', 'tourist')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
                'errors' => [
                    'token' => ['Invalid or expired reset token.']
                ]
            ], 400);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                'password_reset_token' => null,
                'password_reset_token_expires_at' => null
            ]);

            return response()->json([
                'message' => 'Password reset successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password reset failed.',
                'errors' => [
                    'general' => ['An error occurred while resetting your password. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Create new password for unverified users
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function createPassword(Request $request): JsonResponse
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
                'token' => 'required|string',
                'password' => 'required|min:8|confirmed'
            ], [
                'token.required' => 'The setup token is required.',
                'token.string' => 'The setup token must be a string.',
                'password.required' => 'The password field is required.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.confirmed' => 'The password confirmation does not match.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        $user = User::where('password_setup_token', $request->token)
            ->where('password_setup_token_expires_at', '>', Carbon::now())
            ->where('user_type', 'tourist')
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid or expired setup token.',
                'errors' => [
                    'token' => ['Invalid or expired setup token.']
                ]
            ], 400);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                'password_setup_token' => null,
                'password_setup_token_expires_at' => null,
                'is_verified' => true,
                'email_verified_at' => Carbon::now()
            ]);

            return response()->json([
                'message' => 'Password created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password creation failed.',
                'errors' => [
                    'general' => ['An error occurred while creating your password. Please try again.']
                ]
            ], 500);
        }
    }

    /**
     * Change password
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request): JsonResponse
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
                'current_password' => 'required|string',
                'password' => 'required|min:8|confirmed',
            ], [
                'current_password.required' => 'The current password field is required.',
                'current_password.string' => 'The current password must be a string.',
                'password.required' => 'The new password field is required.',
                'password.min' => 'The new password must be at least 8 characters.',
                'password.confirmed' => 'The new password confirmation does not match.'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'The provided password does not match your current password.',
                    'errors' => [
                        'current_password' => ['The provided password does not match your current password.']
                    ]
                ], 401);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'message' => 'Password changed successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Password change failed.',
                'errors' => [
                    'general' => ['An error occurred while changing your password. Please try again.']
                ]
            ], 500);
        }
    }
} 