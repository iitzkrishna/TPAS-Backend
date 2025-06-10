<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Models\Service;
use App\Models\ServicePackageImage;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    /**
     * The storage disk to use for service images
     */
    protected $storageDisk = 'public';

    /**
     * The base path for service images
     */
    protected $imageBasePath = 'services';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
        $this->middleware('service.provider');
    }

    /**
     * Store an image and return its path
     */
    protected function storeImage($file, $type = 'images')
    {
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw ValidationException::withMessages([
                'file' => ['Invalid image format. Allowed formats: JPEG, PNG, GIF, WEBP'],
            ]);
        }

        // Generate unique filename with original extension
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::random(40) . '.' . $extension;
        
        // Store the file
        $path = $file->storeAs(
            "{$this->imageBasePath}/{$type}",
            $filename,
            $this->storageDisk
        );

        if (!$path) {
            throw ValidationException::withMessages([
                'file' => ['Failed to store image. Please try again.'],
            ]);
        }

        return $path;
    }

    /**
     * Delete an image from storage
     */
    protected function deleteImage($path)
    {
        if ($path && Storage::disk($this->storageDisk)->exists($path)) {
            Storage::disk($this->storageDisk)->delete($path);
        }
    }

    /**
     * Get the full URL for an image
     */
    protected function getImageUrl($path)
    {
        return $path ? Storage::disk($this->storageDisk)->url($path) : null;
    }

    /**
     * List all services for the authenticated partner
     */
    public function index(Request $request)
    {
        $query = Auth::user()->serviceProvider->services();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status_visibility', $request->status);
        }

        // Filter by district
        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $services = $query->paginate(10);

        // Add full URLs for images
        $services->getCollection()->transform(function ($service) {
            $service->thumbnail_url = $this->getImageUrl($service->thumbnail);
            $service->images->transform(function ($image) {
                $image->url = $this->getImageUrl($image->image_key);
                return $image;
            });
            return $service;
        });

        return response()->json([
            'services' => $services
        ]);
    }

    /**
     * Create a new service
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:tour,accommodation,transport,activity,other',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'thumbnail' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif,webp', // 2MB max
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_expires_on' => 'nullable|date|after:today',
            'status_visibility' => 'required|in:active,inactive,draft',
            'location_latitude' => 'required|numeric',
            'location_longitude' => 'required|numeric',
            'district_id' => 'required|exists:districts,id',
            'availability' => 'nullable|array',
            'images' => 'nullable|array|max:10', // Maximum 10 images
            'images.*' => 'image|max:2048|mimes:jpeg,png,jpg,gif,webp' // 2MB max per image
        ]);

        try {
            $service = Auth::user()->serviceProvider->services()->create($request->except(['thumbnail', 'images']));

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $path = $this->storeImage($request->file('thumbnail'), 'thumbnails');
                $service->update(['thumbnail' => $path]);
            }

            // Handle multiple images upload
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $this->storeImage($image);
                    $service->images()->create(['image_key' => $path]);
                }
            }

            // Load relationships and add URLs
            $service->load('images');
            $service->thumbnail_url = $this->getImageUrl($service->thumbnail);
            $service->images->transform(function ($image) {
                $image->url = $this->getImageUrl($image->image_key);
                return $image;
            });

            return response()->json([
                'message' => 'Service created successfully',
                'service' => $service
            ], 201);
        } catch (\Exception $e) {
            // Clean up any uploaded files if service creation fails
            if (isset($service)) {
                $this->deleteImage($service->thumbnail);
                foreach ($service->images as $image) {
                    $this->deleteImage($image->image_key);
                }
                $service->delete();
            }
            
            throw ValidationException::withMessages([
                'error' => ['Failed to create service: ' . $e->getMessage()],
            ]);
        }
    }

    /**
     * Update a service
     */
    public function update(Request $request, Service $service)
    {
        // Check if the service belongs to the authenticated partner
        if ($service->sp_id !== Auth::user()->serviceProvider->id) {
            throw ValidationException::withMessages([
                'service' => ['You are not authorized to update this service.'],
            ]);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:tour,accommodation,transport,activity,other',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'thumbnail' => 'nullable|image|max:2048',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_expires_on' => 'nullable|date|after:today',
            'status_visibility' => 'sometimes|in:active,inactive,draft',
            'location_latitude' => 'sometimes|numeric',
            'location_longitude' => 'sometimes|numeric',
            'district_id' => 'sometimes|exists:districts,id',
            'availability' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048'
        ]);

        $service->update($request->except(['thumbnail', 'images']));

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            $this->deleteImage($service->thumbnail);
            $path = $this->storeImage($request->file('thumbnail'), 'thumbnails');
            $service->update(['thumbnail' => $path]);
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->storeImage($image);
                $service->images()->create(['image_key' => $path]);
            }
        }

        // Load relationships and add URLs
        $service->load('images');
        $service->thumbnail_url = $this->getImageUrl($service->thumbnail);
        $service->images->transform(function ($image) {
            $image->url = $this->getImageUrl($image->image_key);
            return $image;
        });

        return response()->json([
            'message' => 'Service updated successfully',
            'service' => $service
        ]);
    }

    /**
     * Delete a service
     */
    public function destroy(Service $service)
    {
        // Check if the service belongs to the authenticated partner
        if ($service->sp_id !== Auth::user()->serviceProvider->id) {
            throw ValidationException::withMessages([
                'service' => ['You are not authorized to delete this service.'],
            ]);
        }

        // Delete thumbnail
        $this->deleteImage($service->thumbnail);

        // Delete all images
        foreach ($service->images as $image) {
            $this->deleteImage($image->image_key);
            $image->delete();
        }

        $service->delete();

        return response()->json([
            'message' => 'Service deleted successfully'
        ]);
    }

    /**
     * Get service details
     */
    public function show(Service $service)
    {
        $service->load(['images', 'reviews']);
        $service->thumbnail_url = $this->getImageUrl($service->thumbnail);
        $service->images->transform(function ($image) {
            $image->url = $this->getImageUrl($image->image_key);
            return $image;
        });

        return response()->json([
            'service' => $service
        ]);
    }

    /**
     * Delete a service image
     */
    public function deleteServiceImage(Service $service, ServicePackageImage $image)
    {
        // Check if the service belongs to the authenticated partner
        if ($service->sp_id !== Auth::user()->serviceProvider->id) {
            throw ValidationException::withMessages([
                'service' => ['You are not authorized to delete this image.'],
            ]);
        }

        // Check if the image belongs to the service
        if ($image->service_id !== $service->id) {
            throw ValidationException::withMessages([
                'image' => ['This image does not belong to the specified service.'],
            ]);
        }

        $this->deleteImage($image->image_key);
        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully'
        ]);
    }
} 