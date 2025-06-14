<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServicePackageReview;
use App\Models\Tourist;
use Carbon\Carbon;

class ServicePackageReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active services
        $services = Service::where('status_visibility', 'active')->get();

        if ($services->isEmpty()) {
            $this->command->info('No active services found. Please run ServiceSeeder first.');
            return;
        }

        // Get all tourists
        $tourists = Tourist::all();

        if ($tourists->isEmpty()) {
            $this->command->info('No tourists found. Please run TouristSeeder first.');
            return;
        }

        // Sample review data
        $reviews = [
            // 5-star reviews
            [
                'title' => 'Excellent Experience',
                'rating' => 5.0,
                'review' => 'Absolutely amazing experience! The service exceeded our expectations in every way. Highly recommended!'
            ],
            [
                'title' => 'Perfect Stay',
                'rating' => 5.0,
                'review' => 'Everything was perfect from start to finish. The staff was incredibly attentive and the facilities were top-notch.'
            ],
            [
                'title' => 'Best Service Ever',
                'rating' => 5.0,
                'review' => 'Couldn\'t have asked for a better experience. The attention to detail was impressive and the service was impeccable.'
            ],
            [
                'title' => 'Memorable Experience',
                'rating' => 5.0,
                'review' => 'One of the best experiences we\'ve had. The quality of service and attention to detail was outstanding.'
            ],
            [
                'title' => 'Exceptional Service',
                'rating' => 5.0,
                'review' => 'The service was exceptional in every way. Staff went above and beyond to make our stay perfect.'
            ],

            // 4.5-star reviews
            [
                'title' => 'Great Value for Money',
                'rating' => 4.5,
                'review' => 'Very good service with reasonable pricing. The staff was friendly and professional.'
            ],
            [
                'title' => 'Wonderful Experience',
                'rating' => 4.5,
                'review' => 'Had a wonderful time. The service was great and the location was perfect. Would definitely recommend.'
            ],
            [
                'title' => 'Excellent Service',
                'rating' => 4.5,
                'review' => 'The service was excellent and the staff was very accommodating. Great value for the price.'
            ],
            [
                'title' => 'Perfect for Family',
                'rating' => 4.5,
                'review' => 'Great for family trips. Kids loved it and we felt very comfortable throughout our stay.'
            ],
            [
                'title' => 'Beautiful Location',
                'rating' => 4.5,
                'review' => 'The location was stunning and the service matched the beautiful surroundings.'
            ],

            // 4-star reviews
            [
                'title' => 'Good but Could Be Better',
                'rating' => 4.0,
                'review' => 'Overall a good experience. Some minor issues that could be improved, but still worth trying.'
            ],
            [
                'title' => 'Nice Experience',
                'rating' => 4.0,
                'review' => 'Had a nice time. The service was good and the staff was friendly. Would consider coming back.'
            ],
            [
                'title' => 'Decent Service',
                'rating' => 4.0,
                'review' => 'The service was decent and met our expectations. Nothing extraordinary but gets the job done.'
            ],
            [
                'title' => 'Good Value',
                'rating' => 4.0,
                'review' => 'Good value for the price. The service was adequate and the staff was helpful.'
            ],
            [
                'title' => 'Pleasant Stay',
                'rating' => 4.0,
                'review' => 'Had a pleasant stay. The service was good and the location was convenient.'
            ],

            // 3.5-star reviews
            [
                'title' => 'Satisfactory Service',
                'rating' => 3.5,
                'review' => 'Decent service for the price. Nothing extraordinary but gets the job done.'
            ],
            [
                'title' => 'Average Experience',
                'rating' => 3.5,
                'review' => 'The experience was average. Service was okay but could use some improvements.'
            ],
            [
                'title' => 'Acceptable Service',
                'rating' => 3.5,
                'review' => 'The service was acceptable. Not great but not bad either. Met our basic needs.'
            ],
            [
                'title' => 'Basic but Functional',
                'rating' => 3.5,
                'review' => 'Basic service that gets the job done. Nothing fancy but functional.'
            ],
            [
                'title' => 'Mediocre Experience',
                'rating' => 3.5,
                'review' => 'The experience was mediocre. Service was adequate but nothing special.'
            ]
        ];

        // Create reviews for each service
        foreach ($services as $service) {
            // Randomly select 5-15 reviews for each service
            $selectedReviews = collect($reviews)->random(rand(5, 15));
            
            foreach ($selectedReviews as $reviewData) {
                ServicePackageReview::create([
                    'service_id' => $service->id,
                    'tourist_id' => $tourists->random()->id, // Randomly select a tourist
                    ...$reviewData,
                    'created_at' => Carbon::now()->subDays(rand(1, 90)), // Random date within last 90 days
                    'updated_at' => Carbon::now()->subDays(rand(1, 90))
                ]);
            }
        }
    }
} 
