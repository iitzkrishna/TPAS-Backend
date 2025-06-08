<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::factory()->admin()->create([
            'user_name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('Admin@23!'),
            'is_verified' => true,
        ]);

        // Create 50 service providers
        User::factory()->serviceProvider()->count(50)->create();

        // Create 100 tourists
        User::factory()->tourist()->count(100)->create();
    }
}
