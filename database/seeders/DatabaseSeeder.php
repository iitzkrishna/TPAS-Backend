<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Tourist;
use App\Models\Partner;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Create admin user
        $adminUser = User::factory()->admin()->create([
            'user_name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin@23!'),
            'is_verified' => true,
        ]);

        // Create admin record
        Admin::create([
            'user_id' => $adminUser->id
        ]);

        // Create partner users and records
        $partnerUsers = User::factory()->partner()->count(50)->create();
        foreach ($partnerUsers as $user) {
            Partner::create([
                'user_id' => $user->id,
                'business_name' => fake()->company(),
                'business_registration_number' => fake()->unique()->numerify('BRN#######'),
                'business_address' => fake()->address(),
                'business_phone' => fake()->phoneNumber(),
                'business_email' => fake()->companyEmail(),
                'business_website' => fake()->url(),
                'business_description' => fake()->paragraph(),
                'business_logo' => fake()->imageUrl(200, 200, 'business'),
                'status' => fake()->randomElement(['pending', 'active', 'suspended', 'rejected'])
            ]);
        }

        // Create tourist users and records
        $touristUsers = User::factory()->tourist()->count(100)->create();
        foreach ($touristUsers as $user) {
            Tourist::create([
                'user_id' => $user->id
            ]);
        }

        $this->call([
            LocationSeeder::class,
            ServiceSeeder::class,
            ServicePackageReviewSeeder::class,
            ServiceBookingSeeder::class,
            ServiceWishlistSeeder::class,
        ]);
    }
}
