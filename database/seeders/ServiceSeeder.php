<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Partner;
use App\Models\District;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get only active partners
        $partners = Partner::where('status', 'active')->get();

        if ($partners->isEmpty()) {
            $this->command->info('No active partners found. Please run PartnerSeeder first and ensure some partners are active.');
            return;
        }

        // Get all districts
        $districts = District::all();

        if ($districts->isEmpty()) {
            $this->command->info('No districts found. Please run LocationSeeder first.');
            return;
        }

        // Sample data for different service types
        $services = [
            // Tours
            [
                'title' => 'Colombo City Tour',
                'type' => 'tour',
                'amount' => 2500.00,
                'description' => 'Experience the vibrant city of Colombo with our guided tour. Visit historical landmarks, local markets, and enjoy authentic Sri Lankan cuisine.',
                'discount_percentage' => 10,
                'discount_expires_on' => Carbon::now()->addDays(30),
                'status_visibility' => 'active',
                'location' => 'Fort, Colombo',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['09:00-17:00'],
                    'tuesday' => ['09:00-17:00'],
                    'wednesday' => ['09:00-17:00'],
                    'thursday' => ['09:00-17:00'],
                    'friday' => ['09:00-17:00']
                ]
            ],
            [
                'title' => 'Kandy Cultural Experience',
                'type' => 'tour',
                'amount' => 3500.00,
                'description' => 'Immerse yourself in the rich cultural heritage of Kandy. Visit the Temple of the Tooth, enjoy traditional dance performances, and explore local crafts.',
                'status_visibility' => 'active',
                'location' => 'Temple of the Tooth, Kandy',
                'district_id' => $districts->where('district_name', 'Kandy')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-18:00'],
                    'wednesday' => ['08:00-18:00'],
                    'friday' => ['08:00-18:00']
                ]
            ],

            // Accommodations
            [
                'title' => 'Beachfront Villa',
                'type' => 'accommodation',
                'amount' => 15000.00,
                'description' => 'Luxurious beachfront villa with private pool, ocean view, and modern amenities. Perfect for a family vacation or romantic getaway.',
                'discount_percentage' => 15,
                'discount_expires_on' => Carbon::now()->addDays(15),
                'status_visibility' => 'active',
                'location' => 'Unawatuna Beach, Galle',
                'district_id' => $districts->where('district_name', 'Galle')->first()->district_id,
                'availability' => [
                    'monday' => ['14:00-12:00'],
                    'tuesday' => ['14:00-12:00'],
                    'wednesday' => ['14:00-12:00'],
                    'thursday' => ['14:00-12:00'],
                    'friday' => ['14:00-12:00'],
                    'saturday' => ['14:00-12:00'],
                    'sunday' => ['14:00-12:00']
                ]
            ],
            [
                'title' => 'Mountain View Cottage',
                'type' => 'accommodation',
                'amount' => 8000.00,
                'description' => 'Cozy cottage with panoramic mountain views. Enjoy the cool climate and peaceful surroundings of the hill country.',
                'status_visibility' => 'active',
                'location' => 'Lake Gregory, Nuwara Eliya',
                'district_id' => $districts->where('district_name', 'Nuwara Eliya')->first()->district_id,
                'availability' => [
                    'monday' => ['14:00-12:00'],
                    'tuesday' => ['14:00-12:00'],
                    'wednesday' => ['14:00-12:00'],
                    'thursday' => ['14:00-12:00'],
                    'friday' => ['14:00-12:00'],
                    'saturday' => ['14:00-12:00'],
                    'sunday' => ['14:00-12:00']
                ]
            ],

            // Transport
            [
                'title' => 'Airport Transfer Service',
                'type' => 'transport',
                'amount' => 3000.00,
                'description' => 'Comfortable and reliable airport transfer service. Available 24/7 with professional drivers and modern vehicles.',
                'status_visibility' => 'active',
                'location' => 'Bandaranaike International Airport, Katunayake',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['00:00-23:59'],
                    'tuesday' => ['00:00-23:59'],
                    'wednesday' => ['00:00-23:59'],
                    'thursday' => ['00:00-23:59'],
                    'friday' => ['00:00-23:59'],
                    'saturday' => ['00:00-23:59'],
                    'sunday' => ['00:00-23:59']
                ]
            ],
            [
                'title' => 'City Taxi Service',
                'type' => 'transport',
                'amount' => 500.00,
                'description' => 'Quick and convenient taxi service for city travel. Clean vehicles and experienced drivers.',
                'status_visibility' => 'active',
                'location' => 'Colombo City Center',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['06:00-22:00'],
                    'tuesday' => ['06:00-22:00'],
                    'wednesday' => ['06:00-22:00'],
                    'thursday' => ['06:00-22:00'],
                    'friday' => ['06:00-22:00'],
                    'saturday' => ['06:00-22:00'],
                    'sunday' => ['06:00-22:00']
                ]
            ],

            // Activities
            [
                'title' => 'Surfing Lessons',
                'type' => 'activity',
                'amount' => 2000.00,
                'description' => 'Learn to surf with professional instructors. Suitable for beginners and intermediate surfers.',
                'discount_percentage' => 20,
                'discount_expires_on' => Carbon::now()->addDays(7),
                'status_visibility' => 'active',
                'location' => 'Hikkaduwa Beach',
                'district_id' => $districts->where('district_name', 'Galle')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-16:00'],
                    'wednesday' => ['08:00-16:00'],
                    'friday' => ['08:00-16:00']
                ]
            ],
            [
                'title' => 'Yoga Retreat',
                'type' => 'activity',
                'amount' => 1500.00,
                'description' => 'Daily yoga sessions in a peaceful environment. Suitable for all levels.',
                'status_visibility' => 'active',
                'location' => 'Viharamahadevi Park, Colombo',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['07:00-09:00', '17:00-19:00'],
                    'wednesday' => ['07:00-09:00', '17:00-19:00'],
                    'friday' => ['07:00-09:00', '17:00-19:00']
                ]
            ]
        ];

        // Create services for each active partner
        foreach ($partners as $partner) {
            // Randomly select 2-4 services for each partner
            $selectedServices = collect($services)->random(rand(2, 4));
            
            foreach ($selectedServices as $serviceData) {
                Service::create([
                    'partner_id' => $partner->id,
                    ...$serviceData
                ]);
            }
        }

    }
} 