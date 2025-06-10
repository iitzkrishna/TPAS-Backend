<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with user_type 'partner'
        $partnerUsers = User::where('user_type', 'partner')->get();

        if ($partnerUsers->isEmpty()) {
            $this->command->info('No partner users found. Please run UserFactory with partner() first.');
            return;
        }

        // Create partner records for each partner user
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

        $this->command->info('Partners seeded successfully!');
    }
} 