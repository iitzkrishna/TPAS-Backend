<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Tourist;
use App\Models\ServiceWishlist;
use Carbon\Carbon;

class ServiceWishlistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tourists
        $tourists = Tourist::all();
        
        // Get all active services
        $services = Service::where('status_visibility', 'active')->get();

        if ($tourists->isEmpty() || $services->isEmpty()) {
            $this->command->info('No tourists or active services found. Please run TouristSeeder and ServiceSeeder first.');
            return;
        }

        // Sample review data
        $reviews = [
            [
                'rating' => 5.0,
                'review' => 'This is definitely going to be my next destination! The photos look amazing and the reviews are outstanding.'
            ],
            [
                'rating' => 4.5,
                'review' => 'Great place to visit. The location is perfect and the amenities look promising.'
            ],
            [
                'rating' => 4.0,
                'review' => 'Looks like a wonderful experience. Can\'t wait to try it out!'
            ],
            [
                'rating' => 5.0,
                'review' => 'This is exactly what I\'ve been looking for. The price is reasonable and the service seems top-notch.'
            ],
            [
                'rating' => 4.5,
                'review' => 'Perfect for my upcoming trip. The location and facilities are exactly what I need.'
            ]
        ];

        // Create wishlist items for each tourist
        foreach ($tourists as $tourist) {
            // Randomly select 3-8 services for each tourist's wishlist
            $selectedServices = $services->random(rand(3, 8));
            
            foreach ($selectedServices as $service) {
                // Randomly decide if this wishlist item should have a rating and review
                $hasReview = rand(0, 1);
                $reviewData = $hasReview ? collect($reviews)->random() : null;

                ServiceWishlist::create([
                    'service_id' => $service->id,
                    'tourist_id' => $tourist->id,
                    'rating' => $reviewData ? $reviewData['rating'] : null,
                    'review' => $reviewData ? $reviewData['review'] : null,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)), // Random date within last 30 days
                    'updated_at' => Carbon::now()->subDays(rand(1, 30))
                ]);
            }
        }
    }
} 