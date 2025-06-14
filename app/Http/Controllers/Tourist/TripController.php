<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TripController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function planTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destinations' => 'required|array',
            'destinations.*' => 'exists:districts,district_id',
            'startDate' => 'required|date|after:today',
            'endDate' => 'required|date|after:startDate',
            'tripType' => 'required|in:solo,partner,friends,family',
            'interests' => 'required|array',
            'interests.*' => 'exists:interests,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tourist = Auth::user()->tourist;

        $trip = Trip::create([
            'tourist_id' => $tourist->id,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'trip_type' => $request->tripType,
            'status' => 'pending'
        ]);

        // Attach destinations
        $trip->destinations()->attach($request->destinations);

        // Attach interests
        $trip->interests()->attach($request->interests);

        // Create initial prompt
        $prompt = TripPrompt::create([
            'trip_id' => $trip->id,
            'prompt' => $this->generatePrompt($trip),
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Trip plan created successfully',
            'trip' => $trip->load(['destinations', 'interests', 'prompts'])
        ], 201);
    }

    public function getTrip($id)
    {
        $trip = Trip::with(['destinations', 'interests', 'prompts'])
            ->where('tourist_id', Auth::user()->tourist->id)
            ->findOrFail($id);

        return response()->json(['trip' => $trip]);
    }

    public function getCompletedTrips()
    {
        $trips = Trip::with(['destinations', 'interests', 'prompts'])
            ->where('tourist_id', Auth::user()->tourist->id)
            ->where('is_completed', true)
            ->get();

        return response()->json(['trips' => $trips]);
    }

    private function generatePrompt(Trip $trip)
    {
        $destinations = $trip->destinations->pluck('district_name')->join(', ');
        $interests = $trip->interests->pluck('name')->join(', ');
        
        return "Plan a {$trip->trip_type} trip to {$destinations} from {$trip->start_date} to {$trip->end_date}. " .
               "Interests include: {$interests}. " .
               "Please provide a detailed itinerary with daily activities, recommended places to visit, " .
               "and suggestions for accommodation and transportation.";
    }
} 