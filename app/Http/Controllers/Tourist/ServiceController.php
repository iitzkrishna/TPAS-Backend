<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\CancelRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    /**
     * Constructor to apply JWT middleware
     */
    public function __construct()
    {
        $this->middleware('jwt');
    }

    /**
     * Get active bookings for the authenticated tourist
     */
    public function getActiveBookings(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'sort_by' => 'sometimes|in:pref_start_date,pref_end_date,created_at,total_charge',
                'sort_order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $tourist = auth()->user()->tourist;

            if (!$tourist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tourist profile not found'
                ], 404);
            }

            $query = ServiceBooking::with(['service' => function($query) {
                    $query->select('id', 'title', 'type', 'subtype', 'amount', 'thumbnail', 'description', 'location', 'district_id')
                        ->with(['district:district_id,district_name', 'images']);
                }])
                ->where('tourist_id', $tourist->id)
                ->where('pref_end_date', '>=', Carbon::now())
                ->where('request', '!=', 'canceled');

            // Apply sorting
            $sortBy = $request->get('sort_by', 'pref_start_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 10);
            $bookings = $query->paginate($perPage);

            // Transform the response
            $bookings->getCollection()->transform(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service' => [
                        'id' => $booking->service->id,
                        'title' => $booking->service->title,
                        'type' => $booking->service->type,
                        'subtype' => $booking->service->subtype,
                        'amount' => $booking->service->amount,
                        'thumbnail' => $booking->service->thumbnail,
                        'description' => $booking->service->description,
                        'location' => $booking->service->location,
                        'district' => $booking->service->district ? $booking->service->district->district_name : null,
                        'images' => $booking->service->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'url' => $image->image_key
                            ];
                        })
                    ],
                    'request' => $booking->request,
                    'pref_start_date' => $booking->pref_start_date,
                    'pref_end_date' => $booking->pref_end_date,
                    'adults' => $booking->adults,
                    'childrens' => $booking->childrens,
                    'total_charge' => $booking->total_charge,
                    'created_at' => $booking->created_at,
                    'cancel_request' => $booking->cancelRequest ? [
                        'id' => $booking->cancelRequest->id,
                        'reason' => $booking->cancelRequest->reason,
                        'status' => $booking->cancelRequest->status,
                        'created_at' => $booking->cancelRequest->created_at
                    ] : null
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch active bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get past bookings for the authenticated tourist
     */
    public function getPastBookings(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'sort_by' => 'sometimes|in:pref_start_date,pref_end_date,created_at,total_charge',
                'sort_order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $tourist = auth()->user()->tourist;

            if (!$tourist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tourist profile not found'
                ], 404);
            }

            $query = ServiceBooking::with(['service' => function($query) {
                    $query->select('id', 'title', 'type', 'subtype', 'amount', 'thumbnail', 'description', 'location', 'district_id')
                        ->with(['district:district_id,district_name', 'images']);
                }])
                ->where('tourist_id', $tourist->id)
                ->where('pref_end_date', '<', Carbon::now())
                ->where('request', '!=', 'canceled');

            // Apply sorting
            $sortBy = $request->get('sort_by', 'pref_end_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 10);
            $bookings = $query->paginate($perPage);

            // Transform the response
            $bookings->getCollection()->transform(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service' => [
                        'id' => $booking->service->id,
                        'title' => $booking->service->title,
                        'type' => $booking->service->type,
                        'subtype' => $booking->service->subtype,
                        'amount' => $booking->service->amount,
                        'thumbnail' => $booking->service->thumbnail,
                        'description' => $booking->service->description,
                        'location' => $booking->service->location,
                        'district' => $booking->service->district ? $booking->service->district->district_name : null,
                        'images' => $booking->service->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'url' => $image->image_key
                            ];
                        })
                    ],
                    'request' => $booking->request,
                    'pref_start_date' => $booking->pref_start_date,
                    'pref_end_date' => $booking->pref_end_date,
                    'adults' => $booking->adults,
                    'childrens' => $booking->childrens,
                    'total_charge' => $booking->total_charge,
                    'created_at' => $booking->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch past bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get canceled bookings for the authenticated tourist
     */
    public function getCanceledBookings(Request $request)
    {
        try {
            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'sort_by' => 'sometimes|in:pref_start_date,pref_end_date,created_at,total_charge',
                'sort_order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:50'
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $tourist = auth()->user()->tourist;

            if (!$tourist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tourist profile not found'
                ], 404);
            }

            $query = ServiceBooking::with(['service' => function($query) {
                    $query->select('id', 'title', 'type', 'subtype', 'amount', 'thumbnail', 'description', 'location', 'district_id')
                        ->with(['district:district_id,district_name', 'images']);
                }])
                ->where('tourist_id', $tourist->id)
                ->where('request', 'canceled');

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->get('per_page', 10);
            $bookings = $query->paginate($perPage);

            // Transform the response
            $bookings->getCollection()->transform(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service' => [
                        'id' => $booking->service->id,
                        'title' => $booking->service->title,
                        'type' => $booking->service->type,
                        'subtype' => $booking->service->subtype,
                        'amount' => $booking->service->amount,
                        'thumbnail' => $booking->service->thumbnail,
                        'description' => $booking->service->description,
                        'location' => $booking->service->location,
                        'district' => $booking->service->district ? $booking->service->district->district_name : null,
                        'images' => $booking->service->images->map(function ($image) {
                            return [
                                'id' => $image->id,
                                'url' => $image->image_key
                            ];
                        })
                    ],
                    'request' => $booking->request,
                    'pref_start_date' => $booking->pref_start_date,
                    'pref_end_date' => $booking->pref_end_date,
                    'adults' => $booking->adults,
                    'childrens' => $booking->childrens,
                    'total_charge' => $booking->total_charge,
                    'created_at' => $booking->created_at,
                    'cancel_request' => $booking->cancelRequest ? [
                        'id' => $booking->cancelRequest->id,
                        'reason' => $booking->cancelRequest->reason,
                        'status' => $booking->cancelRequest->status,
                        'created_at' => $booking->cancelRequest->created_at
                    ] : null
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $bookings->items(),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'per_page' => $bookings->perPage(),
                    'total' => $bookings->total(),
                    'last_page' => $bookings->lastPage()
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch canceled bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booking details
     */
    public function getBookingDetails(ServiceBooking $booking)
    {
        try {
            // Check if the booking belongs to the authenticated tourist
            if ($booking->tourist_id !== auth()->user()->tourist->id) {
                throw new AuthorizationException('Unauthorized to view this booking');
            }

            $booking->load(['service' => function($query) {
                $query->select('id', 'title', 'type', 'subtype', 'amount', 'thumbnail', 'description', 'location', 'district_id')
                    ->with(['district:district_id,district_name', 'images']);
            }]);

            $response = [
                'id' => $booking->id,
                'service' => [
                    'id' => $booking->service->id,
                    'title' => $booking->service->title,
                    'type' => $booking->service->type,
                    'subtype' => $booking->service->subtype,
                    'amount' => $booking->service->amount,
                    'thumbnail' => $booking->service->thumbnail,
                    'description' => $booking->service->description,
                    'location' => $booking->service->location,
                    'district' => $booking->service->district ? $booking->service->district->district_name : null,
                    'images' => $booking->service->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => $image->image_key
                        ];
                    })
                ],
                'request' => $booking->request,
                'pref_start_date' => $booking->pref_start_date,
                'pref_end_date' => $booking->pref_end_date,
                'adults' => $booking->adults,
                'childrens' => $booking->childrens,
                'total_charge' => $booking->total_charge,
                'created_at' => $booking->created_at,
                'cancel_request' => $booking->cancelRequest ? [
                    'id' => $booking->cancelRequest->id,
                    'reason' => $booking->cancelRequest->reason,
                    'status' => $booking->cancelRequest->status,
                    'created_at' => $booking->cancelRequest->created_at
                ] : null
            ];

            return response()->json([
                'status' => 'success',
                'data' => $response
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking not found'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch booking details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
