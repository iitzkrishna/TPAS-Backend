<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\User;
use App\Models\Tourist;
use App\Models\ServiceBooking;
use App\Models\CancelRequest;
use Carbon\Carbon;

class ServiceBookingSeeder extends Seeder
{
    public function run(): void
    {
        // Get all tourist records
        $tourists = Tourist::all();
        
        // Get all services
        $services = Service::all();

        if ($tourists->isEmpty() || $services->isEmpty()) {
            return;
        }

        // Create multiple bookings for each tourist
        foreach ($tourists as $tourist) {
            // Create 2-4 bookings per tourist
            $numBookings = rand(2, 4);
            
            for ($i = 0; $i < $numBookings; $i++) {
                $service = $services->random();
                $startDate = Carbon::now()->addDays(rand(1, 30));
                $endDate = $startDate->copy()->addDays(rand(1, 7));
                
                // Calculate total charge with some variations
                $baseAmount = $service->amount;
                $adults = rand(1, 4);
                $children = rand(0, 3);
                $totalCharge = $baseAmount * $adults + ($baseAmount * 0.5 * $children);
                
                // Add some random discount (0-20%)
                if (rand(0, 1)) {
                    $discount = rand(5, 20) / 100;
                    $totalCharge = $totalCharge * (1 - $discount);
                }

                $booking = ServiceBooking::create([
                    'service_id' => $service->id,
                    'tourist_id' => $tourist->id,
                    'request' => $this->getRandomStatus(),
                    'pref_start_date' => $startDate,
                    'pref_end_date' => $endDate,
                    'adults' => $adults,
                    'childrens' => $children,
                    'total_charge' => round($totalCharge, 2),
                ]);

                // If booking is cancelled, create cancel request
                if ($booking->request === 'cancelled') {
                    $this->createCancelRequest($booking);
                }
            }

            // Create some past bookings
            $this->createPastBookings($tourist, $services);
        }
    }

    private function getRandomStatus(): string
    {
        $statuses = ['pending', 'approved', 'cancelled', 'completed'];
        $weights = [30, 40, 15, 15]; // Probability weights for each status
        
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $current = 0;
        
        foreach ($statuses as $index => $status) {
            $current += $weights[$index];
            if ($rand <= $current) {
                return $status;
            }
        }
        
        return 'pending'; // Default fallback
    }

    private function createCancelRequest(ServiceBooking $booking): void
    {
        $reasons = [
            'Change of travel plans',
            'Found better alternative',
            'Emergency situation',
            'Weather conditions',
            'Personal reasons',
            'Schedule conflict',
            'Price concerns'
        ];

        $statuses = ['pending', 'approved', 'rejected'];
        $weights = [20, 60, 20]; // Probability weights for each status
        
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $current = 0;
        $status = 'pending';
        
        foreach ($statuses as $index => $s) {
            $current += $weights[$index];
            if ($rand <= $current) {
                $status = $s;
                break;
            }
        }

        CancelRequest::create([
            'service_booking_id' => $booking->id,
            'reason' => $reasons[array_rand($reasons)],
            'status' => $status,
            'approved_by' => $status !== 'pending' ? User::where('user_type', 'admin')->first()?->id : null,
        ]);
    }

    private function createPastBookings(Tourist $tourist, $services): void
    {
        // Create 3-5 past bookings
        $numPastBookings = rand(3, 5);
        
        for ($i = 0; $i < $numPastBookings; $i++) {
            $service = $services->random();
            $startDate = Carbon::now()->subDays(rand(30, 180)); // Past 1-6 months
            $endDate = $startDate->copy()->addDays(rand(1, 7));
            
            $adults = rand(1, 4);
            $children = rand(0, 3);
            $totalCharge = $service->amount * $adults + ($service->amount * 0.5 * $children);
            
            ServiceBooking::create([
                'service_id' => $service->id,
                'tourist_id' => $tourist->id,
                'request' => 'completed',
                'pref_start_date' => $startDate,
                'pref_end_date' => $endDate,
                'adults' => $adults,
                'childrens' => $children,
                'total_charge' => round($totalCharge, 2),
            ]);
        }
    }
} 