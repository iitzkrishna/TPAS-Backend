<?php

namespace App\Http\Controllers\Tourist;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceWishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceWishlistController extends Controller
{
    /**
     * Add a service to wishlist
     */
    public function addToWishlist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tourist = auth()->user()->tourist;

        // Check if already in wishlist
        $existing = ServiceWishlist::where('service_id', $request->service_id)
            ->where('tourist_id', $tourist->id)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service is already in your wishlist'
            ], 400);
        }

        // Add to wishlist
        $wishlist = ServiceWishlist::create([
            'service_id' => $request->service_id,
            'tourist_id' => $tourist->id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Service added to wishlist successfully',
            'data' => $wishlist
        ]);
    }

    /**
     * Remove a service from wishlist
     */
    public function removeFromWishlist(Service $service)
    {
        $tourist = auth()->user()->tourist;

        $wishlist = ServiceWishlist::where('service_id', $service->id)
            ->where('tourist_id', $tourist->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service is not in your wishlist'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service removed from wishlist successfully'
        ]);
    }

    /**
     * Get tourist's wishlist
     */
    public function getWishlist(Request $request)
    {
        $tourist = auth()->user()->tourist;

        $wishlist = ServiceWishlist::with(['service' => function($query) {
                $query->select('id', 'title', 'type', 'subtype', 'amount', 'thumbnail', 'description', 'location', 'district_id')
                    ->with(['district:district_id,district_name', 'images']);
            }])
            ->where('tourist_id', $tourist->id)
            ->paginate(10);

        // Transform the response
        $wishlist->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'service' => [
                    'id' => $item->service->id,
                    'title' => $item->service->title,
                    'type' => $item->service->type,
                    'subtype' => $item->service->subtype,
                    'amount' => $item->service->amount,
                    'thumbnail' => $item->service->thumbnail,
                    'description' => $item->service->description,
                    'location' => $item->service->location,
                    'district' => $item->service->district ? $item->service->district->district_name : null,
                    'images' => $item->service->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => $image->image_key
                        ];
                    })
                ],
                'rating' => $item->rating,
                'review' => $item->review,
                'created_at' => $item->created_at
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $wishlist->items(),
            'pagination' => [
                'current_page' => $wishlist->currentPage(),
                'per_page' => $wishlist->perPage(),
                'total' => $wishlist->total(),
                'last_page' => $wishlist->lastPage()
            ]
        ]);
    }

    /**
     * Add or update rating and review for a wishlist item
     */
    public function addRatingAndReview(Request $request, Service $service)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|numeric|min:1|max:5',
            'review' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $tourist = auth()->user()->tourist;

        $wishlist = ServiceWishlist::where('service_id', $service->id)
            ->where('tourist_id', $tourist->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service is not in your wishlist'
            ], 404);
        }

        $wishlist->update([
            'rating' => $request->rating,
            'review' => $request->review
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Rating and review updated successfully',
            'data' => $wishlist
        ]);
    }
} 