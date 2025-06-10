<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * List all active services with optional filters
     */
    public function index(Request $request)
    {
        $query = Service::with(['partner', 'district', 'images'])
            ->where('status_visibility', 'active');

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
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
        $query->orderBy($sortBy, $sortOrder);

        // Paginate results
        $perPage = $request->get('per_page', 10);
        $services = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $services
        ]);
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

        $service->load(['partner', 'district', 'images', 'reviews']);

        return response()->json([
            'status' => 'success',
            'data' => $service
        ]);
    }
} 