<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        $query = Service::query()
            ->with(['district:district_id,district_name', 'images'])
            ->select([
                'id',
                'partner_id',
                'title',
                'type',
                'subtype',
                'amount',
                'thumbnail',
                'description',
                'discount_percentage',
                'discount_expires_on',
                'status_visibility',
                'location',
                'district_id',
                'availability',
                'created_at',
                'updated_at'
            ]);

        // Apply filters
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        if ($request->has('subtype')) {
            $query->ofSubtype($request->subtype);
        }

        if ($request->has('status')) {
            $query->where('status_visibility', $request->status);
        }

        if ($request->has('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        // Apply sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $services = $query->paginate(15);

        // Transform the response
        $services->getCollection()->transform(function ($service) {
            return [
                'id' => $service->id,
                'title' => $service->title,
                'type' => $service->type,
                'subtype' => $service->subtype,
                'amount' => $service->amount,
                'thumbnail_url' => $this->getImageUrl($service->thumbnail),
                'description' => $service->description,
                'discount_percentage' => $service->discount_percentage,
                'discount_expires_on' => $service->discount_expires_on,
                'status_visibility' => $service->status_visibility,
                'location' => $service->location,
                'district' => [
                    'id' => $service->district_id,
                    'name' => $service->district ? $service->district->district_name : null
                ],
                'availability' => $service->availability,
                'images' => $service->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $this->getImageUrl($image->image_key)
                    ];
                }),
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $services->items(),
            'pagination' => [
                'current_page' => $services->currentPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
                'last_page' => $services->lastPage()
            ]
        ]);
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_keys(Service::getServiceTypes()))],
            'subtype' => ['required', function ($attribute, $value, $fail) use ($request) {
                if (!in_array($value, Service::getSubtypesForType($request->type))) {
                    $fail('The selected subtype is invalid for the given type.');
                }
            }],
            'amount' => 'required|numeric|min:0',
            'thumbnail' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif,webp', // 2MB max
            'description' => 'required|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_expires_on' => 'nullable|date|after:today',
            'status_visibility' => ['required', Rule::in(array_values(Service::getConstants('STATUS_')))],
            'location' => 'required|string',
            'district_id' => 'required|exists:districts,district_id',
            'availability' => 'nullable|array',
            'images' => 'nullable|array|max:10', // Maximum 10 images
            'images.*' => 'image|max:2048|mimes:jpeg,png,jpg,gif,webp' // 2MB max per image
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create service without images first
            $data = $validator->validated();
            $data['partner_id'] = auth()->user()->id;
            $service = Service::create($data);

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
                'status' => 'success',
                'message' => 'Service created successfully',
                'data' => $service
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
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified service.
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
            'status' => 'success',
            'data' => $service
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, Service $service)
    {
        // Check if the service belongs to the authenticated partner
        if ($service->partner_id !== auth()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to update this service'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'type' => ['sometimes', 'required', Rule::in(array_keys(Service::getServiceTypes()))],
            'subtype' => ['sometimes', 'required', function ($attribute, $value, $fail) use ($request) {
                if (!in_array($value, Service::getSubtypesForType($request->type))) {
                    $fail('The selected subtype is invalid for the given type.');
                }
            }],
            'amount' => 'sometimes|required|numeric|min:0',
            'thumbnail' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif,webp',
            'description' => 'sometimes|required|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_expires_on' => 'nullable|date|after:today',
            'status_visibility' => ['sometimes', 'required', Rule::in(array_values(Service::getConstants('STATUS_')))],
            'location' => 'sometimes|required|string',
            'district_id' => 'sometimes|required|exists:districts,district_id',
            'availability' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|max:2048|mimes:jpeg,png,jpg,gif,webp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            $service->update($data);

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
                'status' => 'success',
                'message' => 'Service updated successfully',
                'data' => $service
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service)
    {
        // Check if the service belongs to the authenticated partner
        if ($service->partner_id !== auth()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this service'
            ], 403);
        }

        try {
            // Delete thumbnail
            $this->deleteImage($service->thumbnail);

            // Delete all images
            foreach ($service->images as $image) {
                $this->deleteImage($image->image_key);
                $image->delete();
            }

            $service->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Service deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a service image
     */
    public function deleteServiceImage(Service $service, ServicePackageImage $image)
    {
        // Check if the service belongs to the authenticated partner
        if ($service->partner_id !== auth()->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this image'
            ], 403);
        }

        // Check if the image belongs to the service
        if ($image->service_id !== $service->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'This image does not belong to the specified service'
            ], 403);
        }

        try {
            $this->deleteImage($image->image_key);
            $image->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Image deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available service types and their subtypes.
     */
    public function getServiceTypes()
    {
        $types = Service::getServiceTypes();
        $subtypes = [];

        foreach ($types as $type => $label) {
            $subtypes[$type] = Service::getSubtypesForType($type);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'types' => $types,
                'subtypes' => $subtypes
            ]
        ]);
    }
} 