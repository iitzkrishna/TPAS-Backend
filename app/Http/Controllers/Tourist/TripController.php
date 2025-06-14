<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripPrompt;
use App\Models\TripPlan;
use App\Models\District;
use App\Services\GeminiAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TripController extends Controller
{
    protected $geminiAIService;

    public function __construct(GeminiAIService $geminiAIService)
    {
        $this->middleware('auth:api');
        $this->geminiAIService = $geminiAIService;
    }

    public function planTrip(Request $request)
    {
        Log::info('Starting trip planning process', [
            'user_id' => Auth::id(),
            'destinations' => $request->destinations,
            'trip_type' => $request->tripType
        ]);

        $validator = Validator::make($request->all(), [
            'destinations' => 'required|array',
            'destinations.*' => 'exists:districts,district_id',
            'startDate' => 'required|date|after:today',
            'endDate' => 'required|date|after:startDate',
            'tripType' => 'required|in:solo,partner,friends,family',
            'interests' => 'required|array'
        ]);

        if ($validator->fails()) {
            Log::warning('Trip planning validation failed', [
                'user_id' => Auth::id(),
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $tourist = Auth::user()->tourist;
            $trip = Trip::create([
                'tourist_id' => $tourist->id,
                'start_date' => $request->startDate,
                'end_date' => $request->endDate,
                'trip_type' => $request->tripType,
                'destinations' => $request->destinations,
                'interests' => $request->interests,
                'status' => 'pending'
            ]);

            // Create initial prompt for trip details
            $tripDetailsPrompt = TripPrompt::create([
                'trip_id' => $trip->id,
                'prompt' => $this->generateTripDetailsPrompt($trip),
                'status' => 'pending'
            ]);

            try {
                // Get AI response for trip details
                $tripDetailsResponse = $this->geminiAIService->generateTripPlan($tripDetailsPrompt->prompt);
                
                // Update trip details prompt
                $tripDetailsPrompt->update([
                    'response' => is_array($tripDetailsResponse) ? json_encode($tripDetailsResponse) : $tripDetailsResponse,
                    'status' => 'completed'
                ]);

                // Create prompt for itinerary
                $itineraryPrompt = TripPrompt::create([
                    'trip_id' => $trip->id,
                    'prompt' => $this->generateItineraryPrompt($trip, is_array($tripDetailsResponse) ? json_encode($tripDetailsResponse) : $tripDetailsResponse),
                    'status' => 'pending'
                ]);

                // Get AI response for itinerary
                $itineraryResponse = $this->geminiAIService->generateTripPlan($itineraryPrompt->prompt);
                $tripPlan = is_array($itineraryResponse) ? $itineraryResponse : json_decode($itineraryResponse, true);

                if ($tripPlan && isset($tripPlan['trip_plan'])) {
                    // Store the trip plan
                    TripPlan::create([
                        'trip_id' => $trip->id,
                        'total_days' => $tripPlan['trip_plan']['total_days'],
                        'stay_points' => $tripPlan['trip_plan']['stay_points'],
                        'itinerary' => $tripPlan['trip_plan']['itinerary']
                    ]);

                    // Update itinerary prompt
                    $itineraryPrompt->update([
                        'response' => $itineraryResponse,
                        'status' => 'completed'
                    ]);

                    // Update trip status
                    $trip->update([
                        'status' => 'completed',
                        'is_completed' => true
                    ]);

                    Log::info('Trip plan completed successfully', [
                        'trip_id' => $trip->id,
                        'user_id' => Auth::id()
                    ]);
                } else {
                    throw new \Exception('Invalid response format from AI service');
                }
            } catch (\Exception $e) {
                Log::error('Error during trip planning process', [
                    'trip_id' => $trip->id,
                    'error' => $e->getMessage()
                ]);

                // Update prompt statuses to failed
                $tripDetailsPrompt->update(['status' => 'failed']);
                if (isset($itineraryPrompt)) {
                    $itineraryPrompt->update(['status' => 'failed']);
                }

                // Update trip status to failed
                $trip->update([
                    'status' => 'failed'
                ]);

                return response()->json([
                    'message' => 'Failed to generate trip plan',
                    'error' => $e->getMessage()
                ], 500);
            }

            return response()->json([
                'message' => 'Trip plan created successfully',
                'trip' => $trip->load(['prompts', 'plan'])
            ], 201);

        } catch (\Exception $e) {
            Log::error('Unexpected error in trip planning', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getTrip($id)
    {
        $trip = Trip::with(['prompts', 'plan'])
            ->where('tourist_id', Auth::user()->tourist->id)
            ->findOrFail($id);

        // Get district names for the destinations
        $districtNames = District::whereIn('district_id', $trip->destinations)
            ->pluck('district_name', 'district_id')
            ->toArray();

        $trip->district_names = $districtNames;

        return response()->json(['trip' => $trip]);
    }

    public function getCompletedTrips()
    {
        $trips = Trip::with(['prompts', 'plan'])
            ->where('tourist_id', Auth::user()->tourist->id)
            ->where('is_completed', true)
            ->get();

        // Get all district IDs from all trips
        $allDistrictIds = $trips->pluck('destinations')->flatten()->unique();
        
        // Get district names for all destinations
        $districtNames = District::whereIn('district_id', $allDistrictIds)
            ->pluck('district_name', 'district_id')
            ->toArray();

        // Add district names to each trip
        $trips->each(function ($trip) use ($districtNames) {
            $trip->district_names = $districtNames;
        });

        return response()->json(['trips' => $trips]);
    }

    private function generateTripDetailsPrompt(Trip $trip)
    {
        // Get district names for the destinations
        $districtNames = District::whereIn('district_id', $trip->destinations)
            ->pluck('district_name')
            ->join(', ');
            
        $interests = implode(', ', $trip->interests);
        
        return "Analyze the following trip details and provide recommendations for accommodation and general activities: " .
               "Trip Type: {$trip->trip_type} " .
               "Destinations: {$districtNames} " .
               "Duration: {$trip->start_date} to {$trip->end_date} " .
               "Interests: {$interests} " .
               "Please provide detailed recommendations for: " .
               "1. Suitable accommodation types and areas " .
               "2. General activities that match the interests " .
               "3. Transportation options between destinations " .
               "4. Any special considerations based on the trip type";
    }

    private function generateItineraryPrompt(Trip $trip, $tripDetailsResponse)
    {
        // Get district names for the destinations
        $districtNames = District::whereIn('district_id', $trip->destinations)
            ->pluck('district_name')
            ->join(', ');
            
        $interests = implode(', ', $trip->interests);
        
        return "Based on the following trip details and recommendations, create a detailed daily itinerary: " .
               "Trip Type: {$trip->trip_type} " .
               "Destinations: {$districtNames} " .
               "Duration: {$trip->start_date} to {$trip->end_date} " .
               "Interests: {$interests} " .
               "Previous Recommendations: {$tripDetailsResponse} " .
               "Please format the response as a JSON object with the following structure: " .
               "{\"trip_plan\": {\"total_days\": number, \"stay_points\": [{\"location\": string, \"stay_duration\": number, \"hotel_suggestion\": string}], " .
               "\"itinerary\": [{\"day\": number, \"base\": string, \"places_to_visit\": [{\"name\": string, \"distance_from_base_km\": number, " .
               "\"activities\": [string], \"time_spent\": string}]}]}}";
    }
} 