<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceProviderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('service.provider');
    }

    /**
     * Update business information
     */
    public function updateBusinessInfo(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'business_name' => 'required|string|max:100',
            'business_registration_number' => 'required|string|max:50',
            'business_address' => 'required|string|max:255',
        ]);

        $user->serviceProvider->update($request->only([
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
} 