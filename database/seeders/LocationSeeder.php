<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run()
    {
        // Insert provinces
        $provinces = [
            'Central Province',
            'Eastern Province',
            'North Central Province',
            'Northern Province',
            'North Western Province',
            'Sabaragamuwa Province',
            'Southern Province',
            'Uva Province',
            'Western Province'
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'province_name' => $province,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Insert districts
        $districts = [
            // Central Province
            ['Kandy', 'Central Province'],
            ['Matale', 'Central Province'],
            ['Nuwara Eliya', 'Central Province'],

            // Eastern Province
            ['Ampara', 'Eastern Province'],
            ['Batticaloa', 'Eastern Province'],
            ['Trincomalee', 'Eastern Province'],

            // North Central Province
            ['Anuradhapura', 'North Central Province'],
            ['Polonnaruwa', 'North Central Province'],

            // Northern Province
            ['Jaffna', 'Northern Province'],
            ['Kilinochchi', 'Northern Province'],
            ['Mannar', 'Northern Province'],
            ['Mullaitivu', 'Northern Province'],
            ['Vavuniya', 'Northern Province'],

            // North Western Province
            ['Kurunegala', 'North Western Province'],
            ['Puttalam', 'North Western Province'],

            // Sabaragamuwa Province
            ['Kegalle', 'Sabaragamuwa Province'],
            ['Ratnapura', 'Sabaragamuwa Province'],

            // Southern Province
            ['Galle', 'Southern Province'],
            ['Hambantota', 'Southern Province'],
            ['Matara', 'Southern Province'],

            // Uva Province
            ['Badulla', 'Uva Province'],
            ['Monaragala', 'Uva Province'],

            // Western Province
            ['Colombo', 'Western Province'],
            ['Gampaha', 'Western Province'],
            ['Kalutara', 'Western Province']
        ];

        foreach ($districts as $district) {
            $provinceId = DB::table('provinces')
                ->where('province_name', $district[1])
                ->value('province_id');

            DB::table('districts')->insert([
                'district_name' => $district[0],
                'province_id' => $provinceId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
} 