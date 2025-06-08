<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_booking_id')->constrained('service_booking')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded']);
            $table->enum('mode', ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}; 