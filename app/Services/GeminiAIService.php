<?php

namespace App\Services;

use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class GeminiAIService
{
    protected $apiKey;
    protected $apiUrl;
    protected $httpClient;
    protected $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        if (empty($this->apiKey)) {
            throw new Exception('GEMINI_API_KEY is not set in environment variables');
        }
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $this->apiKey;
        $this->httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10
        ]);
    }

    public function generateTripPlan($prompt) {
        Log::info('Generating trip plan with Gemini AI', ['prompt_length' => strlen($prompt)]);
        
        $structuredPrompt = $this->formatTripPrompt($prompt);

        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $structuredPrompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1, // Even lower temperature for more consistent JSON
                'topK' => 1,
                'topP' => 0.1,
                'maxOutputTokens' => 4096, // Increased token limit
            ]
        ];

        $attempt = 0;
        while ($attempt < $this->maxRetries) {
            try {
                Log::debug('Sending request to Gemini API', ['attempt' => $attempt + 1]);
                $response = $this->httpClient->post($this->apiUrl, [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]);

                $result = json_decode($response->getBody(), true);
                Log::debug('Received response from Gemini API', [
                    'response_status' => $response->getStatusCode(),
                    'attempt' => $attempt + 1
                ]);

                if (isset($result['candidates']) && is_array($result['candidates']) && !empty($result['candidates'])) {
                    foreach ($result['candidates'] as $candidate) {
                        if (isset($candidate['content']['parts']) && is_array($candidate['content']['parts']) && !empty($candidate['content']['parts'])) {
                            foreach ($candidate['content']['parts'] as $part) {
                                if (isset($part['text'])) {
                                    $text = $part['text'];
                                    
                                    // Try to extract JSON if it's wrapped in markdown code blocks
                                    if (preg_match('/```json\n(.*?)\n```/s', $text, $matches)) {
                                        $text = $matches[1];
                                    }
                                    
                                    // Clean the text
                                    $text = trim($text);
                                    
                                    // Try to parse the response as JSON
                                    $jsonResponse = json_decode($text, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        // Validate the JSON structure
                                        if ($this->validateTripPlanStructure($jsonResponse)) {
                                            Log::info('Successfully parsed and validated JSON response from Gemini AI');
                                            return $jsonResponse;
                                        }
                                        Log::warning('JSON structure validation failed', [
                                            'error' => 'Invalid trip plan structure'
                                        ]);
                                    } else {
                                        Log::warning('Failed to parse JSON response', [
                                            'text' => substr($text, 0, 200) . '...',
                                            'error' => json_last_error_msg()
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                
                $attempt++;
                if ($attempt < $this->maxRetries) {
                    Log::info('Retrying request due to invalid response', ['attempt' => $attempt + 1]);
                    sleep(1); // Wait 1 second before retrying
                }
            } catch (Exception $e) {
                Log::error('Error communicating with Gemini API', [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt + 1
                ]);
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    throw new Exception('Error communicating with Gemini API: ' . $e->getMessage());
                }
                sleep(1);
            }
        }
        
        throw new Exception('Failed to generate valid trip plan after ' . $this->maxRetries . ' attempts');
    }

    private function validateTripPlanStructure($json) {
        if (!isset($json['trip_plan'])) {
            return false;
        }

        $required = [
            'total_days',
            'stay_points',
            'itinerary',
            'additional_recommendations'
        ];

        foreach ($required as $field) {
            if (!isset($json['trip_plan'][$field])) {
                return false;
            }
        }

        return true;
    }

    private function formatTripPrompt($inputPrompt) {
        return <<<PROMPT
        You are a travel planning AI assistant. Create a detailed trip plan based on the following input:
        "{$inputPrompt}"

        IMPORTANT: You MUST respond with a valid JSON object that strictly follows the structure below. Do not include any other text or explanations.

        {
          "trip_plan": {
            "total_days": <int>,
            "stay_points": [
              {
                "location": <str>,
                "stay_duration": <int>,
                "hotel_suggestion": <str>,
                "area_description": <str>,
                "transportation_options": [<str>]
              }
            ],
            "itinerary": [
              {
                "day": <int>,
                "base": <str>,
                "places_to_visit": [
                  {
                    "name": <str>,
                    "distance_from_base_km": <float>,
                    "activities": [<str>],
                    "time_spent": <str>,
                    "best_time_to_visit": <str>,
                    "travel_time": <str>,
                    "opening_hours": <str>,
                    "entrance_fee": <str>,
                    "transportation_to": <str>
                  }
                ],
                "meals": {
                  "breakfast": <str>,
                  "lunch": <str>,
                  "dinner": <str>
                }
              }
            ],
            "additional_recommendations": {
              "transportation": [<str>],
              "packing_tips": [<str>],
              "local_customs": [<str>],
              "safety_tips": [<str>],
              "budget_estimates": {
                "accommodation": <str>,
                "meals": <str>,
                "activities": <str>,
                "transportation": <str>
              }
            }
          }
        }

        Rules for the trip plan:
        1. All distances must be realistic and based on actual locations
        2. Group nearby attractions together in each day's plan
        3. Include 3-5 attractions per day with logical sequencing
        4. Consider the trip type and interests when planning activities
        5. Include practical information like travel time and best times to visit
        6. Suggest accommodations based on the trip type and budget
        7. Include local transportation options between attractions
        8. Consider opening hours and best visiting times for attractions
        9. Include meal suggestions that match the trip type and interests
        10. Ensure all JSON values are properly formatted (strings in quotes, numbers without quotes)
        11. Make sure to close all JSON objects and arrays properly
        12. Do not include any trailing commas in arrays or objects

        Remember: Your response must be a valid JSON object that can be parsed by JSON.parse(). Do not include any text outside the JSON structure.
        PROMPT;
    }
}