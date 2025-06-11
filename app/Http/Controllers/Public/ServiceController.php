<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * List all active stays
     */
    public function stays(Request $request)
    {
        $request->merge(['type' => 'stay']);
        return $this->getServices($request);
    }

    /**
     * List all active rentals
     */
    public function rental(Request $request)
    {
        $request->merge(['type' => 'rental']);
        return $this->getServices($request);
    }

    /**
     * List all active attractions
     */
    public function attractions(Request $request)
    {
        $request->merge(['type' => 'attraction']);
        return $this->getServices($request);
    }

    /**
     * Get details of a specific service
     */
    public function show(Service $service)
    {
        if ($service->status_visibility !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found'
            ], 404);
        }

        // Load all related data for single service view
        $service->load([
            'partner' => function($query) {
                $query->select('id', 'business_name', 'business_description', 'business_logo');
            },
            'district',
            'images',
            'reviews' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        // Calculate average rating
        $averageRating = $service->reviews->avg('rating');

        // Get service data without timestamps
        $serviceData = $service->only([
            'id',
            'title',
            'type',
            'subtype',
            'amount',
            'thumbnail',
            'description',
            'discount_percentage',
            'discount_expires_on',
            'location',
            'district_id',
            'status_visibility'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => array_merge($serviceData, [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $service->reviews->count()
            ])
        ]);
    }

    /**
     * Common method to get services with filters
     */
    private function getServices(Request $request)
    {
        $query = Service::select([
            'id',
            'title',
            'type',
            'subtype',
            'amount',
            'thumbnail',
            'description',
            'discount_percentage',
            'discount_expires_on',
            'location',
            'district_id'
        ])
        ->with([
            'district:district_id,district_name',
            'partner:id,business_name,business_logo'
        ])
        ->where('status_visibility', 'active');

        // Add review count and average rating
        $query->withCount('reviews')
            ->withAvg('reviews', 'rating');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by subtype
        if ($request->has('subtype')) {
            $query->where('subtype', $request->subtype);
        }

        // Filter by district
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('amount', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('amount', '<=', $request->max_price);
        }

        // Search by title or location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Handle special sorting for ratings
        if ($sortBy === 'rating') {
            $query->orderBy('reviews_avg_rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate results
        $perPage = $request->get('per_page', 10);
        $services = $query->paginate($perPage);

        // Format the response
        $services->getCollection()->transform(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'type' => $service->type,
                'subtype' => $service->subtype,
                'amount' => $service->amount,
                'thumbnail' => $service->thumbnail,
                'description' => $service->description,
                'discount_percentage' => $service->discount_percentage,
                'discount_expires_on' => $service->discount_expires_on,
                'location' => $service->location,
                'district' => $service->district ? $service->district->district_name : null,
                'partner' => $service->partner ? [
                    'id' => $service->partner->id,
                    'business_name' => $service->partner->business_name,
                    'business_logo' => $service->partner->business_logo
                ] : null,
                'rating' => round($service->reviews_avg_rating ?? 0, 1),
                'total_reviews' => $service->reviews_count ?? 0
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $services->items(),
            'current_page' => $services->currentPage(),
            'per_page' => $services->perPage(),
            'total' => $services->total(),
            'last_page' => $services->lastPage()
        ]);
    }

    /**
     * Get reviews for a specific service
     */
    public function getReviews(Service $service)
    {
        if ($service->status_visibility !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found'
            ], 404);
        }

        $reviews = $service->reviews()
            ->with(['tourist' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'profile_picture');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Transform the reviews to include tourist name
        $reviews->getCollection()->transform(function ($review) {
            return [
                'id' => $review->id,
                'title' => $review->title,
                'rating' => $review->rating,
                'review' => $review->review,
                'created_at' => $review->created_at,
                'tourist' => $review->tourist ? [
                    'id' => $review->tourist->id,
                    'name' => $review->tourist->first_name . ' ' . $review->tourist->last_name,
                    'profile_picture' => $review->tourist->profile_picture
                ] : null
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $reviews->items(),
            'current_page' => $reviews->currentPage(),
            'per_page' => $reviews->perPage(),
            'total' => $reviews->total(),
            'last_page' => $reviews->lastPage()
        ]);
    }
} 