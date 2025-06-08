<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TouristSOSController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('tourist');
    }

    /**
     * Update tourist location and SOS status
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'is_help_needed' => 'required|boolean',
        ]);

        $user = Auth::user();
        $location = $user->tourist->locations()->create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'is_help_needed' => $request->is_help_needed,
        ]);

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => $location
        ]);
    }

    /**
     * Get current location and SOS status
     */
    public function getCurrentLocation()
    {
        $user = Auth::user();
        $location = $user->tourist->locations()->latest()->first();

        return response()->json([
            'location' => $location
        ]);
    }

    /**
     * Get location history
     */
    public function getLocationHistory()
    {
        $user = Auth::user();
        $locations = $user->tourist->locations()->latest()->paginate(10);

        return response()->json([
            'locations' => $locations
        ]);
    }
} 