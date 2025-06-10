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
                $query->select('id', 'business_id')
                    ->with(['business:id,name,description,logo']);
            },
            'district',
            'images',
            'reviews' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        // Calculate average rating
        $averageRating = $service->reviews->avg('rating');

        return response()->json([
            'status' => 'success',
            'data' => array_merge($service->toArray(), [
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
            'district:district_id,district_name'
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
                'district' => $service->district->district_name,
                'rating' => round($service->reviews_avg_rating, 1),
                'total_reviews' => $service->reviews_count
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $services
        ]);
    }
} 