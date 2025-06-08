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

class AuthSPController extends Controller
{
    /**
     * Create a new AuthSPController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword', 'resetPassword', 'verifyEmail', 'createPassword']]);
    }

    /**
     * Register a new service provider
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'nationality' => 'required|string|max:50',
            'phone_number' => 'required|string|max:20',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
            // Optional business info
            'business_name' => 'nullable|string|max:100',
            'business_registration_number' => 'nullable|string|max:50',
            'business_address' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'service_provider',
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

        // Create service provider record
        $user->serviceProvider()->create([
            'business_name' => $request->business_name,
            'business_registration_number' => $request->business_registration_number,
            'business_address' => $request->business_address,
        ]);

        // Send verification email
        Mail::send('emails.verify-email', ['token' => $user->email_verification_token], function($message) use($user) {
            $message->to($user->email);
            $message->subject('Verify Email Address');
        });

        $token = Auth::login($user);

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => $user->load('serviceProvider'),
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ], 201);
    }

    /**
     * Login service provider and create token
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // First check if user exists and is a service provider
        $user = User::where('username', $request->username)
            ->where('user_type', 'service_provider')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'username' => ['No service provider account found with these credentials.'],
            ]);
        }

        // Check if user is verified
        if (!$user->is_verified) {
            throw ValidationException::withMessages([
                'username' => ['Please verify your email before logging in.'],
            ]);
        }

        // Attempt login with credentials
        $credentials = $request->only('username', 'password');
        $credentials['user_type'] = 'service_provider';

        if (!$token = Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        $user = Auth::user();
        $user->load('serviceProvider');

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'business_info' => [
                'is_complete' => !empty($user->serviceProvider->business_name) && 
                               !empty($user->serviceProvider->business_registration_number) && 
                               !empty($user->serviceProvider->business_address),
                'message' => empty($user->serviceProvider->business_name) ? 
                    'Please complete your business profile' : null
            ],
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Logout service provider (Invalidate the token)
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
        $user = Auth::user();
        $user->load('serviceProvider');

        return response()->json([
            'user' => $user,
            'authorization' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    /**
     * Get the authenticated service provider
     */
    public function user()
    {
        $user = Auth::user();
        $user->load('serviceProvider');
        return response()->json($user);
    }

    /**
     * Update service provider profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'sometimes|string|max:50|unique:users,username,' . $user->id,
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'first_name' => 'sometimes|string|max:50',
            'last_name' => 'sometimes|string|max:50',
            'nationality' => 'sometimes|string|max:50',
            'phone_number' => 'sometimes|string|max:20',
            'gender' => 'sometimes|in:male,female,other',
            'date_of_birth' => 'sometimes|date',
        ]);

        $user->update($request->only([
            'username',
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
            'user' => $user->load('serviceProvider')
        ]);
    }

    /**
     * Update business information
     */
    public function updateBusinessInfo(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:100',
            'business_registration_number' => 'required|string|max:50',
            'business_address' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->serviceProvider()->update($request->only([
            'business_name',
            'business_registration_number',
            'business_address',
        ]));

        return response()->json([
            'message' => 'Business information updated successfully',
            'business_info' => $user->serviceProvider
        ]);
    }

    /**
     * Get business information
     */
    public function getBusinessInfo()
    {
        $user = Auth::user();
        return response()->json($user->serviceProvider);
    }

    /**
     * Verify user's email
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $user = User::where('email_verification_token', $request->token)
            ->where('email_verification_token_expires_at', '>', Carbon::now())
            ->where('user_type', 'service_provider')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired verification token.'],
            ]);
        }

        $user->update([
            'is_verified' => true,
            'email_verification_token' => null,
            'email_verification_token_expires_at' => null,
            'email_verified_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Email verified successfully'
        ]);
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)
            ->where('user_type', 'service_provider')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No service provider account found with this email.'],
            ]);
        }

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
        ]);
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::where('password_reset_token', $request->token)
            ->where('password_reset_token_expires_at', '>', Carbon::now())
            ->where('user_type', 'service_provider')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired reset token.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
            'password_reset_token_expires_at' => null
        ]);

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }

    /**
     * Create new password for unverified users
     */
    public function createPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::where('password_setup_token', $request->token)
            ->where('password_setup_token_expires_at', '>', Carbon::now())
            ->where('user_type', 'service_provider')
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired setup token.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_setup_token' => null,
            'password_setup_token_expires_at' => null,
            'is_verified' => true,
            'email_verified_at' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Password created successfully'
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
} 