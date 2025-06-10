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
            // Stays (30+ entries)
            [
                'title' => 'Beachfront Villa',
                'type' => 'stay',
                'subtype' => 'villa',
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
                'type' => 'stay',
                'subtype' => 'guest_house',
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
            [
                'title' => 'Colombo City Hotel',
                'type' => 'stay',
                'subtype' => 'hotel',
                'amount' => 12000.00,
                'description' => 'Modern hotel in the heart of Colombo with city views, spa services, and fine dining restaurants.',
                'discount_percentage' => 20,
                'discount_expires_on' => Carbon::now()->addDays(10),
                'status_visibility' => 'active',
                'location' => 'Marine Drive, Colombo',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
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
                'title' => 'Eco Tree House',
                'type' => 'stay',
                'subtype' => 'villa',
                'amount' => 5000.00,
                'description' => 'Unique tree house experience in a sustainable eco-lodge. Perfect for nature lovers and adventure seekers.',
                'status_visibility' => 'active',
                'location' => 'Sinharaja Forest, Ratnapura',
                'district_id' => $districts->where('district_name', 'Ratnapura')->first()->district_id,
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

            // Additional Stays
            [
                'title' => 'Heritance Kandalama',
                'type' => 'stay',
                'subtype' => 'hotel',
                'amount' => 25000.00,
                'description' => 'World-renowned eco-luxury hotel with stunning views of Kandalama Lake and Sigiriya Rock Fortress.',
                'status_visibility' => 'active',
                'location' => 'Kandalama, Dambulla',
                'district_id' => $districts->where('district_name', 'Matale')->first()->district_id,
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
                'title' => 'Cinnamon Wild Yala',
                'type' => 'stay',
                'subtype' => 'hotel',
                'amount' => 30000.00,
                'description' => 'Luxury safari lodge located on the border of Yala National Park. Experience wildlife from your doorstep.',
                'status_visibility' => 'active',
                'location' => 'Yala National Park, Hambantota',
                'district_id' => $districts->where('district_name', 'Hambantota')->first()->district_id,
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
                'title' => 'Tea Trails Bungalow',
                'type' => 'stay',
                'subtype' => 'villa',
                'amount' => 28000.00,
                'description' => 'Luxury colonial tea planter\'s bungalow in the heart of Ceylon tea country. Experience the charm of old Ceylon.',
                'status_visibility' => 'active',
                'location' => 'Hatton, Nuwara Eliya',
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
            [
                'title' => 'Galle Fort Apartment',
                'type' => 'stay',
                'subtype' => 'apartment',
                'amount' => 12000.00,
                'description' => 'Charming apartment in the historic Galle Fort. Perfect for couples or small families.',
                'status_visibility' => 'active',
                'location' => 'Galle Fort, Galle',
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
                'title' => 'Ella Mountain View Guest House',
                'type' => 'stay',
                'subtype' => 'guest_house',
                'amount' => 8000.00,
                'description' => 'Cozy guest house with panoramic views of Ella Gap. Perfect for nature lovers and hikers.',
                'status_visibility' => 'active',
                'location' => 'Ella Town, Badulla',
                'district_id' => $districts->where('district_name', 'Badulla')->first()->district_id,
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
                'title' => 'Mirissa Beach House',
                'type' => 'stay',
                'subtype' => 'beach_house',
                'amount' => 18000.00,
                'description' => 'Luxurious beach house with private access to Mirissa Beach. Perfect for whale watching and beach activities.',
                'status_visibility' => 'active',
                'location' => 'Mirissa Beach, Matara',
                'district_id' => $districts->where('district_name', 'Matara')->first()->district_id,
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
                'title' => 'Kandy Lake View Hotel',
                'type' => 'stay',
                'subtype' => 'hotel',
                'amount' => 15000.00,
                'description' => 'Elegant hotel overlooking Kandy Lake and the Temple of the Tooth. Perfect for cultural experiences.',
                'status_visibility' => 'active',
                'location' => 'Kandy Lake, Kandy',
                'district_id' => $districts->where('district_name', 'Kandy')->first()->district_id,
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
                'title' => 'Colombo City Apartment',
                'type' => 'stay',
                'subtype' => 'apartment',
                'amount' => 10000.00,
                'description' => 'Modern apartment in the heart of Colombo. Perfect for business travelers or short city stays.',
                'status_visibility' => 'active',
                'location' => 'Colombo 03, Colombo',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
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
                'title' => 'Sigiriya Farm Stay',
                'type' => 'stay',
                'subtype' => 'farmhouse',
                'amount' => 12000.00,
                'description' => 'Authentic farm stay experience near Sigiriya. Enjoy organic meals and rural life.',
                'status_visibility' => 'active',
                'location' => 'Sigiriya, Dambulla',
                'district_id' => $districts->where('district_name', 'Matale')->first()->district_id,
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

            // Rentals (30+ entries)
            [
                'title' => 'Airport Transfer Service',
                'type' => 'rental',
                'subtype' => 'airport-taxi',
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
                'type' => 'rental',
                'subtype' => 'cars',
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
            [
                'title' => 'Luxury Car Rental',
                'type' => 'rental',
                'subtype' => 'cars',
                'amount' => 8000.00,
                'description' => 'Premium car rental service with a fleet of luxury vehicles. Perfect for special occasions or business travel.',
                'discount_percentage' => 10,
                'discount_expires_on' => Carbon::now()->addDays(20),
                'status_visibility' => 'active',
                'location' => 'Colombo City Center',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-20:00'],
                    'tuesday' => ['08:00-20:00'],
                    'wednesday' => ['08:00-20:00'],
                    'thursday' => ['08:00-20:00'],
                    'friday' => ['08:00-20:00'],
                    'saturday' => ['09:00-18:00'],
                    'sunday' => ['09:00-18:00']
                ]
            ],
            [
                'title' => 'Scooter Rental',
                'type' => 'rental',
                'subtype' => 'motorcycles',
                'amount' => 1000.00,
                'description' => 'Affordable scooter rental for exploring the city. Easy to park and perfect for short trips.',
                'status_visibility' => 'active',
                'location' => 'Galle Fort',
                'district_id' => $districts->where('district_name', 'Galle')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-18:00'],
                    'tuesday' => ['08:00-18:00'],
                    'wednesday' => ['08:00-18:00'],
                    'thursday' => ['08:00-18:00'],
                    'friday' => ['08:00-18:00'],
                    'saturday' => ['08:00-18:00'],
                    'sunday' => ['08:00-18:00']
                ]
            ],

            // Additional Rentals
            [
                'title' => 'Luxury Van Rental',
                'type' => 'rental',
                'subtype' => 'vans',
                'amount' => 12000.00,
                'description' => 'Comfortable van rental perfect for group travel. Includes professional driver and air conditioning.',
                'status_visibility' => 'active',
                'location' => 'Colombo City Center',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-20:00'],
                    'tuesday' => ['08:00-20:00'],
                    'wednesday' => ['08:00-20:00'],
                    'thursday' => ['08:00-20:00'],
                    'friday' => ['08:00-20:00'],
                    'saturday' => ['09:00-18:00'],
                    'sunday' => ['09:00-18:00']
                ]
            ],
            [
                'title' => 'Tuk Tuk City Tour',
                'type' => 'rental',
                'subtype' => 'tuktuk',
                'amount' => 2000.00,
                'description' => 'Authentic tuk-tuk experience for city tours. Perfect for short trips and local experience.',
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
            [
                'title' => 'Luxury Yacht Charter',
                'type' => 'rental',
                'subtype' => 'yatch',
                'amount' => 50000.00,
                'description' => 'Exclusive yacht charter for private cruises. Perfect for special occasions and luxury experiences.',
                'status_visibility' => 'active',
                'location' => 'Marina, Colombo',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['09:00-17:00'],
                    'tuesday' => ['09:00-17:00'],
                    'wednesday' => ['09:00-17:00'],
                    'thursday' => ['09:00-17:00'],
                    'friday' => ['09:00-17:00'],
                    'saturday' => ['09:00-17:00'],
                    'sunday' => ['09:00-17:00']
                ]
            ],
            [
                'title' => 'Boat Safari',
                'type' => 'rental',
                'subtype' => 'boat',
                'amount' => 8000.00,
                'description' => 'Boat safari in Madu Ganga. Experience the mangrove forest and wildlife.',
                'status_visibility' => 'active',
                'location' => 'Madu Ganga, Galle',
                'district_id' => $districts->where('district_name', 'Galle')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-17:00'],
                    'tuesday' => ['08:00-17:00'],
                    'wednesday' => ['08:00-17:00'],
                    'thursday' => ['08:00-17:00'],
                    'friday' => ['08:00-17:00'],
                    'saturday' => ['08:00-17:00'],
                    'sunday' => ['08:00-17:00']
                ]
            ],
            [
                'title' => 'Motorcycle Adventure',
                'type' => 'rental',
                'subtype' => 'motorcycles',
                'amount' => 3000.00,
                'description' => 'Adventure motorcycle rental for exploring the hill country. Perfect for experienced riders.',
                'status_visibility' => 'active',
                'location' => 'Nuwara Eliya Town',
                'district_id' => $districts->where('district_name', 'Nuwara Eliya')->first()->district_id,
                'availability' => [
                    'monday' => ['08:00-18:00'],
                    'tuesday' => ['08:00-18:00'],
                    'wednesday' => ['08:00-18:00'],
                    'thursday' => ['08:00-18:00'],
                    'friday' => ['08:00-18:00'],
                    'saturday' => ['08:00-18:00'],
                    'sunday' => ['08:00-18:00']
                ]
            ],

            // Attractions (30+ entries)
            [
                'title' => 'Colombo City Tour',
                'type' => 'attraction',
                'subtype' => 'historical_site',
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
                'type' => 'attraction',
                'subtype' => 'historical_site',
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
            [
                'title' => 'Surfing Lessons',
                'type' => 'attraction',
                'subtype' => 'water_park',
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
                'type' => 'attraction',
                'subtype' => 'other',
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
            ],
            [
                'title' => 'Tea Factory Tour',
                'type' => 'attraction',
                'subtype' => 'museum',
                'amount' => 1800.00,
                'description' => 'Experience the world-famous Ceylon tea production process. Tour the factory, learn about tea making, and enjoy a tea tasting session.',
                'status_visibility' => 'active',
                'location' => 'Pedro Tea Estate, Nuwara Eliya',
                'district_id' => $districts->where('district_name', 'Nuwara Eliya')->first()->district_id,
                'availability' => [
                    'monday' => ['09:00-16:00'],
                    'tuesday' => ['09:00-16:00'],
                    'wednesday' => ['09:00-16:00'],
                    'thursday' => ['09:00-16:00'],
                    'friday' => ['09:00-16:00']
                ]
            ],
            [
                'title' => 'Wildlife Safari',
                'type' => 'attraction',
                'subtype' => 'national_park',
                'amount' => 4500.00,
                'description' => 'Exciting safari experience in Yala National Park. Spot elephants, leopards, and other wildlife in their natural habitat.',
                'discount_percentage' => 15,
                'discount_expires_on' => Carbon::now()->addDays(14),
                'status_visibility' => 'active',
                'location' => 'Yala National Park',
                'district_id' => $districts->where('district_name', 'Hambantota')->first()->district_id,
                'availability' => [
                    'monday' => ['05:00-18:00'],
                    'wednesday' => ['05:00-18:00'],
                    'friday' => ['05:00-18:00'],
                    'saturday' => ['05:00-18:00'],
                    'sunday' => ['05:00-18:00']
                ]
            ],
            [
                'title' => 'Cooking Class',
                'type' => 'attraction',
                'subtype' => 'other',
                'amount' => 3000.00,
                'description' => 'Learn to cook authentic Sri Lankan dishes with a local chef. Includes market tour and recipe booklet.',
                'status_visibility' => 'active',
                'location' => 'Colombo City Center',
                'district_id' => $districts->where('district_name', 'Colombo')->first()->district_id,
                'availability' => [
                    'monday' => ['10:00-14:00'],
                    'wednesday' => ['10:00-14:00'],
                    'friday' => ['10:00-14:00']
                ]
            ],

            // Additional Attractions
            [
                'title' => 'Sigiriya Rock Fortress Tour',
                'type' => 'attraction',
                'subtype' => 'historical_site',
                'amount' => 5000.00,
                'description' => 'Guided tour of the ancient Sigiriya Rock Fortress. Includes historical insights and photography spots.',
                'status_visibility' => 'active',
                'location' => 'Sigiriya, Dambulla',
                'district_id' => $districts->where('district_name', 'Matale')->first()->district_id,
                'availability' => [
                    'monday' => ['06:00-17:00'],
                    'tuesday' => ['06:00-17:00'],
                    'wednesday' => ['06:00-17:00'],
                    'thursday' => ['06:00-17:00'],
                    'friday' => ['06:00-17:00'],
                    'saturday' => ['06:00-17:00'],
                    'sunday' => ['06:00-17:00']
                ]
            ],
            [
                'title' => 'Pinnawala Elephant Orphanage',
                'type' => 'attraction',
                'subtype' => 'zoo',
                'amount' => 3000.00,
                'description' => 'Visit the famous elephant orphanage. Watch elephants bathe and feed them.',
                'status_visibility' => 'active',
                'location' => 'Pinnawala, Kegalle',
                'district_id' => $districts->where('district_name', 'Kegalle')->first()->district_id,
                'availability' => [
                    'monday' => ['08:30-17:30'],
                    'tuesday' => ['08:30-17:30'],
                    'wednesday' => ['08:30-17:30'],
                    'thursday' => ['08:30-17:30'],
                    'friday' => ['08:30-17:30'],
                    'saturday' => ['08:30-17:30'],
                    'sunday' => ['08:30-17:30']
                ]
            ],
            [
                'title' => 'Horton Plains Trek',
                'type' => 'attraction',
                'subtype' => 'national_park',
                'amount' => 4000.00,
                'description' => 'Guided trek through Horton Plains National Park. Visit World\'s End and Baker\'s Falls.',
                'status_visibility' => 'active',
                'location' => 'Horton Plains, Nuwara Eliya',
                'district_id' => $districts->where('district_name', 'Nuwara Eliya')->first()->district_id,
                'availability' => [
                    'monday' => ['06:00-16:00'],
                    'tuesday' => ['06:00-16:00'],
                    'wednesday' => ['06:00-16:00'],
                    'thursday' => ['06:00-16:00'],
                    'friday' => ['06:00-16:00'],
                    'saturday' => ['06:00-16:00'],
                    'sunday' => ['06:00-16:00']
                ]
            ],
            [
                'title' => 'Galle Fort Walking Tour',
                'type' => 'attraction',
                'subtype' => 'historical_site',
                'amount' => 2000.00,
                'description' => 'Guided walking tour of the UNESCO World Heritage site Galle Fort. Learn about colonial history.',
                'status_visibility' => 'active',
                'location' => 'Galle Fort, Galle',
                'district_id' => $districts->where('district_name', 'Galle')->first()->district_id,
                'availability' => [
                    'monday' => ['09:00-17:00'],
                    'tuesday' => ['09:00-17:00'],
                    'wednesday' => ['09:00-17:00'],
                    'thursday' => ['09:00-17:00'],
                    'friday' => ['09:00-17:00'],
                    'saturday' => ['09:00-17:00'],
                    'sunday' => ['09:00-17:00']
                ]
            ],
            [
                'title' => 'Whale Watching Tour',
                'type' => 'attraction',
                'subtype' => 'water_park',
                'amount' => 12000.00,
                'description' => 'Whale watching tour from Mirissa. Spot blue whales and dolphins in their natural habitat.',
                'status_visibility' => 'active',
                'location' => 'Mirissa Harbor, Matara',
                'district_id' => $districts->where('district_name', 'Matara')->first()->district_id,
                'availability' => [
                    'monday' => ['06:00-12:00'],
                    'tuesday' => ['06:00-12:00'],
                    'wednesday' => ['06:00-12:00'],
                    'thursday' => ['06:00-12:00'],
                    'friday' => ['06:00-12:00'],
                    'saturday' => ['06:00-12:00'],
                    'sunday' => ['06:00-12:00']
                ]
            ]
        ];

        // Create services for each active partner
        foreach ($partners as $partner) {
            // Randomly select 5-10 services for each partner
            $selectedServices = collect($services)->random(rand(5, 10));
            
            foreach ($selectedServices as $serviceData) {
                Service::create([
                    'partner_id' => $partner->id,
                    ...$serviceData
                ]);
            }
        }
    }
} 